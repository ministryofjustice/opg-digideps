<?php

namespace AppBundle\Controller;

use AppBundle\Exception as AppException;
use AppBundle\Service\Auth\HeaderTokenAuthenticator;
use AppBundle\Service\Auth\UserProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/auth")
 */
class AuthController extends RestController
{
    /**
     * Return the user by email&password or token
     * expected keys in body: 'token' or ('email' and 'password').
     *
     *
     * @Route("/login")
     * @Method({"POST"})
     */
    public function login(Request $request)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new AppException\UnauthorisedException('client secret not accepted.');
        }
        $data = $this->deserializeBodyContent($request);

        //brute force checks
        $index = array_key_exists('token', $data) ? 'token' : 'email';
        $key = $index . $data[$index];
        $attemptsInTimechecker = $this->get('attemptsInTimeChecker');
        $incrementalWaitingTimechecker = $this->get('attemptsIncrementalWaitingChecker');

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
        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new AppException\UnauthorisedException($user->getRoleName() . ' user role not allowed from this client.');
        }

        // reset counters at successful login
        $attemptsInTimechecker->resetAttempts($key);
        $incrementalWaitingTimechecker->resetAttempts($key);

        $randomToken = $this->getProvider()->generateRandomTokenAndStore($user);
        $user->setLastLoggedIn(new \DateTime());
        $this->get('em')->flush($user);

        // add token into response
        $this->get('kernel.listener.responseConverter')->addResponseModifier(function ($response) use ($randomToken) {
            $response->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $randomToken);
        });

        // needed for redirector
        $this->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * @return UserProvider
     */
    private function getProvider()
    {
        return $this->container->get('user_provider');
    }

    /**
     * Return the user by email and hashed password (or exception if not found).
     *
     *
     * @Route("/logout")
     * @Method({"POST"})
     */
    public function logout(Request $request)
    {
        $authToken = HeaderTokenAuthenticator::getTokenFromRequest($request);

        return $this->getProvider()->removeToken($authToken);
    }

    /**
     * Test endpoint used for testing to check auth permissions.
     *
     * @Route("/get-logged-user")
     * @Method({"GET"})
     */
    public function getLoggedUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
}
