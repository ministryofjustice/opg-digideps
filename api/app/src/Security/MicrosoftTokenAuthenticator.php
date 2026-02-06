<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\UnauthorisedException;
use App\Exception\UserWrongCredentialsException;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class MicrosoftTokenAuthenticator extends AbstractAuthenticator
{
    private string $bruteForceKey = '';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AttemptsInTimeChecker $attemptsInTimechecker,
        private readonly AttemptsIncrementalWaitingChecker $incrementalWaitingTimechecker,
        private readonly AuthService $authService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly LoggerInterface $verboseLogger,
        private readonly Client $httpClient,
        private readonly string $entraUserInfoUrl,
    ) {}

    public function supports(Request $request): ?bool
    {
        return '/auth/login' === $request->getPathInfo()
            && $request->isMethod('POST')
            && $this->hasRequiredLoginDetails($request);
    }

    public function authenticate(Request $request): Passport
    {
        if (!$this->authService->isSecretValid($request)) {
            $this->verboseLogger->warning('Client secret not accepted in LoginRequestAuthenticator');
            throw new UnauthorisedException('client secret not accepted.');
        }

        if (!$this->hasRequiredLoginDetails($request)) {
            $this->verboseLogger->warning('Insufficient login details provided - requires email and password but one, or both, was missing');
            throw new UserNotFoundException('User not found');
        }

        $data = json_decode($request->getContent(), true);
        $accessToken = $data['entraAccessToken'];

        try {
            $msMeRequest = $this->httpClient->request('GET', $this->entraUserInfoUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
        } catch (GuzzleException $e) {
            $this->verboseLogger->warning('Access token rejected by SSO provider: ' . $e->getMessage());

            if ($e instanceof ClientException) {
                $response = $e->getResponse();
                if ($response) {
                    $responseBody = $response->getBody()->getContents();
                    $this->verboseLogger->warning('SSO provider response: ' . $responseBody);
                }
            } else {
                $this->verboseLogger->warning($e::class);
            }

            throw new UserNotFoundException('User not found');
        }

        $msUser = json_decode($msMeRequest->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $email = strtolower($msUser['mail'] ?? '');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user instanceof \App\Entity\User) {
            $request->attributes->set('user_id', $user->getId());
        } else {
            $request->attributes->set('user_id', null);
            $this->verboseLogger->warning(sprintf('User with email "%s" not found in LoginRequestAuthenticator', $email));
            throw new UserNotFoundException('User not found');
        }

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            $this->verboseLogger->warning(sprintf('Secret not valid for email "%s" with role "%s" in LoginRequestAuthenticator', $email, $user->getRoleName()));

            throw new UnauthorisedException($user->getRoleName() . ' user role not allowed from this client.');
        }

        return new SelfValidatingPassport(
            new UserBadge($email)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $userId = $request->attributes->get('user_id');

        $this->verboseLogger->notice('Successful login', [
            'user_id'   => $userId,
        ]);

        $this->tokenStorage->setToken($token);
        $this->attemptsInTimechecker->resetAttempts($this->bruteForceKey);
        $this->incrementalWaitingTimechecker->resetAttempts($this->bruteForceKey);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $userId = $request->attributes->get('user_id');

        $this->verboseLogger->notice('Failed login', [
            'user_id'   => $userId,
            'reason'    => $exception->getMessage(),
        ]);

        if ($this->attemptsInTimechecker->maxAttemptsReached($this->bruteForceKey)) {
            throw $exception;
        }

        throw new UserWrongCredentialsException($exception->getMessage(), $exception->getCode());
    }

    private function hasRequiredLoginDetails(Request $request): bool
    {
        if (empty($request->getContent())) {
            return false;
        }

        $body = json_decode($request->getContent(), true);

        if (empty($body['entraAccessToken'])) {
            return false;
        }

        return true;
    }
}
