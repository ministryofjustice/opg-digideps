<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\RedisStorage;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class MicrosoftAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RestClient $restClient,
        private readonly RedisStorage $tokenStorage,
        private readonly RouterInterface $router,
        private readonly string $environment,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_microsoft_check';
    }

    public function authenticate(Request $request): Passport
    {
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\MicrosoftClient $client */
        $client = $this->clientRegistry->getClient('office365');
        $msUser = $client->fetchUser();

        // TODO: need to have our own auth client and user type so we can extract email from correct `/me` field

        return new Passport(
            new UserBadge('admin@publicguardian.gov.uk', function ($userEmail) {
                try {
                    [$user, $authToken] = $this->restClient->login(['email' => $userEmail, 'password' => 'DigidepsPass1234']);

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
            }, ''),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('homepage');

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/connect/microsoft', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
