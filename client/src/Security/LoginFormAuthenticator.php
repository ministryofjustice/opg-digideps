<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\UserWrongCredentialsException;
use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\RedisStorage;
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
    public function __construct(
        private RestClient $restClient,
        private Redirector $redirector,
        private RedisStorage $tokenStorage
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return '/login' === $request->getPathInfo() && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->get('login')['email'];
        $password = $request->get('login')['password'];

        return new Passport(
            new UserBadge($email, function ($userEmail) use ($password) {
                [$user, $authToken] = $this->restClient->login(['email' => $userEmail, 'password' => $password]);

                if (!$user) {
                    throw new UserNotFoundException('User not found');
                }

                $this->tokenStorage->set((string) $user->getId(), $authToken);

                return $user;
            }),
            new CustomCredentials(function ($password) {
                // We check credentials in API so as long as that returns then we can assume they are valid
                return true;
            }, $password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $request->getSession()->remove('login-context');

        $redirectUrl = $this->redirector->getFirstPageAfterLogin($request->getSession());
        $this->redirector->removeLastAccessedUrl(); // avoid this URL to be used a the next login

        return new RedirectResponse($redirectUrl, Response::HTTP_FOUND);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new UserWrongCredentialsException($exception->getMessage());
    }
}