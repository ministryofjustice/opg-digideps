<?php

namespace App\Controller;

use App\Entity\User;
use App\EventListener\RestInputOuputFormatter;
use App\Exception as AppException;
use App\Repository\UserRepository;
use App\Security\HeaderTokenAuthenticator;
use App\Security\RedisUserProvider;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    public function __construct(
        private AuthService $authService,
        private RestFormatter $restFormatter,
        private UserRepository $userRepository
    ) {
    }

    /**
     * Return the user by email&password or token
     * expected keys in body: 'token' or ('email' and 'password').
     *
     * @Route("/login", methods={"POST"})
     *
     * @return User|bool|null
     */
    public function login(
        Request $request,
        AttemptsInTimeChecker $attemptsInTimechecker,
        AttemptsIncrementalWaitingChecker $incrementalWaitingTimechecker,
        RestInputOuputFormatter $restInputOutputFormatter,
        EntityManagerInterface $em,
        Client $redis,
        TokenStorageInterface $tokenStorage
    ) {
        if (!$this->authService->isSecretValid($request)) {
            throw new AppException\UnauthorisedException('client secret not accepted.');
        }
        $data = $this->restFormatter->deserializeBodyContent($request);

        // brute force checks
        $index = array_key_exists('token', $data) ? 'token' : 'email';
        $key = $index.$data[$index];

        $attemptsInTimechecker->registerAttempt($key); // e.g emailName@example.org
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
            $user = $this->authService->getUserByToken($data['token']);
        } elseif ($tokenStorage->getToken()->getUser()) {
            $user = $tokenStorage->getToken()->getUser();
        } else {
            // incase the user is not found or the password is not valid (same error given for security reasons)
            if ($attemptsInTimechecker->maxAttemptsReached($key)) {
                throw new AppException\UserWrongCredentialsManyAttempts();
            } else {
                throw new AppException\UserWrongCredentialsException();
            }
        }

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new AppException\UnauthorisedException($user->getRoleName().' user role not allowed from this client.');
        }

        // reset counters at successful login
        $attemptsInTimechecker->resetAttempts($key);
        $incrementalWaitingTimechecker->resetAttempts($key);

        $user->setLastLoggedIn(new \DateTime());
        $em->persist($user);
        $em->flush();

        $authToken = $user->getId().'_'.sha1(microtime().spl_object_hash($user).rand(1, 999));
        $redis->set($authToken, serialize($tokenStorage->getToken()));

        // add token into response
        $restInputOutputFormatter->addResponseModifier(function ($response) use ($authToken) {
            $response->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $authToken);
        });

        // needed for redirector
        $this->restFormatter->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * Return the user by email and hashed password (or exception if not found).
     *
     * @Route("/logout", methods={"POST"})
     */
    public function logout(Request $request, RedisUserProvider $userProvider)
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
        $this->restFormatter->setJmsSerialiserGroups(['user', 'user-login']);

        return $tokenStorage->getToken()->getUser();
    }
}
