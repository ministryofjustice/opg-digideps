<?php

namespace AppBundle\Controller;

use AppBundle\EventListener\RestInputOuputFormatter;
use AppBundle\Exception as AppException;
use AppBundle\Service\Auth\HeaderTokenAuthenticator;
use AppBundle\Service\Auth\UserProvider;
use AppBundle\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use AppBundle\Service\BruteForce\AttemptsInTimeChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    public function __construct(RestInputOuputFormatter $formatter)
    {
        parent::__construct($formatter);
    }

    /**
     * Return the user by email&password or token
     * expected keys in body: 'token' or ('email' and 'password').
     *
     * @Route("/login", methods={"POST"})
     * @param Request $request
     * @param UserProvider $userProvider
     * @param AttemptsInTimeChecker $attemptsInTimechecker
     * @param AttemptsIncrementalWaitingChecker $incrementalWaitingTimechecker
     * @param RestInputOuputFormatter $restInputOuputFormatter
     * @param EntityManagerInterface $em
     * @return \AppBundle\Entity\User|bool|null
     */
    public function login(
        Request $request,
        UserProvider $userProvider,
        AttemptsInTimeChecker $attemptsInTimechecker,
        AttemptsIncrementalWaitingChecker $incrementalWaitingTimechecker,
        RestInputOuputFormatter $restInputOuputFormatter,
        EntityManagerInterface $em
    ) {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new AppException\UnauthorisedException('client secret not accepted.');
        }
        $data = $this->deserializeBodyContent($request);

        //brute force checks
        $index = array_key_exists('token', $data) ? 'token' : 'email';
        $key = $index . $data[$index];

        $attemptsInTimechecker->registerAttempt($key); //e.g emailName@example.org
        $incrementalWaitingTimechecker->registerAttempt($key);

        // exception if reached delay-check
        if ($incrementalWaitingTimechecker->isFrozen($key)) {
            $nextAttemptAt = $incrementalWaitingTimechecker->getUnfrozenAt($key);
            $nextAttemptIn = ceil(($nextAttemptAt - time()) / 60);
            $exception = new AppException\UnauthorisedException("Attack detected. Please try again in $nextAttemptIn minutes", 423);
            $exception->setData($nextAttemptAt);

            throw $exception;
        }

        // load user by credentials (token or username & password)
        if (array_key_exists('token', $data)) {
            $user = $this->getAuthService()->getUserByToken($data['token']);
        } else {
            $user = $this->getAuthService()->getUserByEmailAndPassword(strtolower($data['email']), $data['password']);
        }

        if (!$user) {
            // incase the user is not found or the password is not valid (same error given for security reasons)
            if ($attemptsInTimechecker->maxAttemptsReached($key)) {
                throw new AppException\UserWrongCredentialsManyAttempts();
            } else {
                throw new AppException\UserWrongCredentials();
            }
        }
        if (!$this->getAuthService()->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new AppException\UnauthorisedException($user->getRoleName() . ' user role not allowed from this client.');
        }

        // reset counters at successful login
        $attemptsInTimechecker->resetAttempts($key);
        $incrementalWaitingTimechecker->resetAttempts($key);

        $randomToken = $userProvider->generateRandomTokenAndStore($user);
        $user->setLastLoggedIn(new \DateTime());
        $em->persist($user);
        $em->flush();

        // add token into response
        $restInputOuputFormatter->addResponseModifier(function ($response) use ($randomToken) {
            $response->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $randomToken);
        });

        // needed for redirector
        $this->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * Return the user by email and hashed password (or exception if not found).
     *
     *
     * @Route("/logout", methods={"POST"})
     */
    public function logout(Request $request, UserProvider $userProvider)
    {
        $authToken = HeaderTokenAuthenticator::getTokenFromRequest($request);

        return $userProvider->removeToken($authToken);
    }

    /**
     * Test endpoint used for testing to check auth permissions.
     *
     * @Route("/get-logged-user", methods={"GET"})
     */
    public function getLoggedUser(TokenStorageInterface $tokenStorage)
    {
        $this->setJmsSerialiserGroups(['user']);

        return $tokenStorage->getToken()->getUser();
    }
}
