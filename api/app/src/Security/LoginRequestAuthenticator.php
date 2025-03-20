<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\UnauthorisedException;
use App\Exception\UserWrongCredentialsException;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use App\Service\DateTimeProvider;
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

class LoginRequestAuthenticator extends AbstractAuthenticator
{
    private string $bruteForceKey = '';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AttemptsInTimeChecker $attemptsInTimechecker,
        private readonly AttemptsIncrementalWaitingChecker $incrementalWaitingTimechecker,
        private readonly AuthService $authService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly LoggerInterface $logger,
        private readonly DateTimeProvider $dateTimeProvider
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return '/auth/login' === $request->getPathInfo()
            && $request->isMethod('POST')
            && $this->hasRequiredLoginDetails($request);
    }

    public function authenticate(Request $request): Passport
    {
        if (!$this->authService->isSecretValid($request)) {
            $this->logger->warning('Client secret not accepted in LoginRequestAuthenticator');
            throw new UnauthorisedException('client secret not accepted.');
        }

        if (!$this->hasRequiredLoginDetails($request)) {
            $this->logger->warning('Insufficient login details provided - requires email and password but one, or both, was missing');
            throw new UserNotFoundException('User not found');
        }

        $data = json_decode($request->getContent(), true);
        $email = strtolower($data['email']);
        $password = $data['password'];

        // brute force checks
        $this->bruteForceKey = 'email'.$data['email'];

        $this->attemptsInTimechecker->registerAttempt($this->bruteForceKey); // e.g emailName@example.org
        $this->incrementalWaitingTimechecker->registerAttempt($this->bruteForceKey);

        // exception if reached delay-check
        if ($this->incrementalWaitingTimechecker->isFrozen($this->bruteForceKey)) {
            $nextAttemptAt = $this->incrementalWaitingTimechecker->getUnfrozenAt($this->bruteForceKey);
            $nowTime = intval($this->dateTimeProvider->getDateTime()->format('U'));
            $nextAttemptIn = ceil(($nextAttemptAt - $nowTime) / 60);
            $exception = new UnauthorisedException("Attack detected. Please try again in $nextAttemptIn minutes", 423);
            $exception->setData($nextAttemptIn);

            $this->logger->warning(sprintf('Brute force limit reached in LoginRequestAuthenticator for email "%s"', $email));

            throw $exception;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $this->logger->warning(sprintf('User with email "%s" not found in LoginRequestAuthenticator', $email));
            throw new UserNotFoundException('User not found');
        }

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            $this->logger->warning(sprintf('Secret not valid for email "%s" with role "%s" in LoginRequestAuthenticator', $email, $user->getRoleName()));

            throw new UnauthorisedException($user->getRoleName().' user role not allowed from this client.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->tokenStorage->setToken($token);
        $this->attemptsInTimechecker->resetAttempts($this->bruteForceKey);
        $this->incrementalWaitingTimechecker->resetAttempts($this->bruteForceKey);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
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

        $emailKeyPresent = isset($body['email']) ?? false;
        $passwordKeyPresent = isset($body['password']) ?? false;

        if (!$emailKeyPresent || !$passwordKeyPresent) {
            return false;
        }

        if (empty($body['email']) || empty($body['password'])) {
            return false;
        }

        return true;
    }
}
