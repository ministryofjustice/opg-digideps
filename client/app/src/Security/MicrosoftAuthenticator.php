<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\RedisStorage;
use App\Service\Redirector;
use App\Validator\RouteValidator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Uid\Uuid;

class MicrosoftAuthenticator extends OAuth2Authenticator //implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly LoggerInterface $logger,
        private readonly Redirector $redirector,
        private readonly RestClient $restClient,
        private readonly RedisStorage $tokenStorage,
        private readonly RouterInterface $router,
        private readonly string $environment,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_entra_check' && $this->environment === 'admin';
    }

    public function authenticate(Request $request): Passport
    {
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\MicrosoftClient $client */
        $client = $this->clientRegistry->getClient('entra');

        try {
            $msUser = $client->getAccessToken();
        } catch (IdentityProviderException $e) {
            $this->logger->warning('Failed to get access token from Microsoft', ['exception' => $e]);

            throw new AuthenticationException('Failed to login with Microsoft', 0, $e);
        }

        /**
         * @var User $user
         * @var string $authToken
         */
        [$user, $authToken] = $this->restClient->login(['entraAccessToken' => $msUser->getToken()]);

        return new Passport(
            new UserBadge($user->getEmail(), function () use ($user, $authToken) {
                try {
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
        $session = $request->getSession();

        // Generate a new, random, non-auth trace ID
        $sessionSafeId = Uuid::v4()->toRfc4122();

        // Add it to the session as we will use this for adding to logs (no real need to add to redis)
        $session->set('session_safe_id', $sessionSafeId);

        $redirectUrl = $this->redirector->getFirstPageAfterLogin($session);

        if ($request->query->has('lastPage')) {
            $decodedURL = urldecode($request->query->get('lastPage'));
            if (RouteValidator::validateRoute($this->router, $decodedURL)) {
                $redirectUrl = $decodedURL;
            }
        }

        $this->redirector->removeLastAccessedUrl(); // avoid this URL to be used at the next login

        return new RedirectResponse($redirectUrl, Response::HTTP_FOUND);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse(
            $this->router->generate('login')
        );
    }

    /**
     * This won't do anything unless the Authenticator implements `AuthenticationEntryPointInterface`
     * At that point, it will automatically intercept unauthenticated requests and redirect to via
     * Microsoft, which would provide a seamless transition for people already logged in.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/connect/entra', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
