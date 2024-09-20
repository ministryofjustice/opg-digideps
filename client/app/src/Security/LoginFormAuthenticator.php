<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\RedisStorage;
use App\Service\Redirector;
use App\Validator\RouteValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private RestClient $restClient,
        private Redirector $redirector,
        private RedisStorage $tokenStorage,
        private RouterInterface $router
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
        $csrfToken = $request->request->get('login')['_token'];

        if ('' === $email || null === $email || '' === $password || null === $password) {
            throw new BadCredentialsException('Missing username or password');
        }

        return new Passport(
            new UserBadge($email, function ($userEmail) use ($password) {
                try {
                    [$user, $authToken] = $this->restClient->login(['email' => $userEmail, 'password' => $password]);

                    if (!$user) {
                        throw new UserNotFoundException('User not found');
                    }

                    $this->tokenStorage->set((string) $user->getId(), $authToken);

                    return $user;
                } catch (AuthenticationException $e) {
                    throw $e;
                }
            }),
            new CustomCredentials(function ($password) {
                // We check credentials in API so as long as that returns then we can assume they are valid
                return true;
            }, $password),
            [
                new CsrfTokenBadge('ddloginform', $csrfToken),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        file_put_contents('php://stderr', print_r('**** Inside onAuthenticationSuccess ****', true));
        $redirectUrl = $this->redirector->getFirstPageAfterLogin($request->getSession());

        if ($request->query->has('lastPage')) {
            $decodedURL = urldecode($request->query->get('lastPage'));
            if (RouteValidator::validateRoute($this->router, $decodedURL)) {
                $redirectUrl = $decodedURL;
            }
        }

        $this->redirector->removeLastAccessedUrl(); // avoid this URL to be used a the next login

        return new RedirectResponse($redirectUrl, Response::HTTP_FOUND);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse(
            $this->router->generate('login')
        );
    }
}
