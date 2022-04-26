<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Redirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    public function __construct(private UserApi $userApi, private RestClient $restClient, private Redirector $redirector)
    {
    }

    public function supports(Request $request): ?bool
    {
        return '/login' === $request->getPathInfo() && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        return new Passport(
            new UserBadge($email, function ($userEmail) use ($password) {
                $user = $this->restClient->login(['email' => $userEmail, 'password' => $password]);

                if (!$user) {
                    throw new UserNotFoundException('User not found');
                }

                return $user;
            }),
            new CustomCredentials(function ($credentials, User $user) {
                return true;
            }, $password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // store auth token? See how this works in symfony casts...
//        $tokenVal = $response->getHeader(self::HEADER_AUTH_TOKEN);
//        $tokenVal = is_array($tokenVal) && !empty($tokenVal[0]) ? $tokenVal[0] : null;
//        $this->tokenStorage->set($user->getId(), $tokenVal);

        $redirectUrl = $this->redirector->getFirstPageAfterLogin($request->getSession());
        $this->redirector->removeLastAccessedUrl(); // avoid this URL to be used a the next login

        // 'login-context' determines a one-time message that may have been displayed during login. Remove to prevent showing again.
        $request->getSession()->remove('login-context');

        return new RedirectResponse($redirectUrl, Response::HTTP_FOUND);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        dd('client loginformauth failed');
    }
}
