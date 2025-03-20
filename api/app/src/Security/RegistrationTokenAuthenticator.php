<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Exception\InvalidRegistrationTokenException;
use App\Exception\UnauthorisedException;
use App\Exception\UserWrongCredentialsManyAttempts;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class RegistrationTokenAuthenticator extends AbstractAuthenticator
{
    private string $bruteForceKey = '';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthService $authService,
        private readonly AttemptsInTimeChecker $attemptsInTimeChecker,
        private readonly AttemptsIncrementalWaitingChecker $incrementalWaitingTimeChecker,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->isLoginRouteWithRequiredData($request) || $this->isFirstPasswordSetRouteWithRequiredData($request);
    }

    public function authenticate(Request $request)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $data = json_decode($request->getContent(), true);
        $token = $data['token'];

        // brute force checks
        $this->setBruteForceKey('token'.$token);

        $this->attemptsInTimeChecker->registerAttempt($this->bruteForceKey);
        $this->incrementalWaitingTimeChecker->registerAttempt($this->bruteForceKey);

        // exception if reached delay-check
        if ($this->incrementalWaitingTimeChecker->isFrozen($this->bruteForceKey)) {
            $nextAttemptAt = $this->incrementalWaitingTimeChecker->getUnfrozenAt($this->bruteForceKey);
            $nextAttemptIn = ceil(($nextAttemptAt - time()) / 60);
            $exception = new UnauthorisedException("Attack detected. Please try again in $nextAttemptIn minutes", 423);
            $exception->setData($nextAttemptAt);

            throw $exception;
        }

        $user = $this->userRepository->findOneBy(['registrationToken' => $token]);

        if (!$user instanceof User) {
            throw new UserNotFoundException('User not found');
        }

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new UnauthorisedException($user->getRoleName().' user role not allowed from this client.');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getEmail()),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->attemptsInTimeChecker->resetAttempts($this->bruteForceKey);
        $this->incrementalWaitingTimeChecker->resetAttempts($this->bruteForceKey);
        $this->tokenStorage->setToken($token);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($this->attemptsInTimeChecker->maxAttemptsReached($this->bruteForceKey)) {
            throw new UserWrongCredentialsManyAttempts();
        }

        throw new InvalidRegistrationTokenException($exception->getMessage(), $exception->getCode());
    }

    private function isLoginRouteWithRequiredData(Request $request): bool
    {
        return '/auth/login' === $request->getPathInfo()
        && $request->isMethod('POST')
        && $this->requestHasToken($request);
    }

    private function isFirstPasswordSetRouteWithRequiredData(Request $request): bool
    {
        if (empty($request->getContent())) {
            return false;
        }

        $body = json_decode($request->getContent(), true);

        $token = $body['token'] ?? false;
        $password = $body['password'] ?? false;

        if (!$password || !$token) {
            return false;
        }

        $userId = $this->userRepository->findOneBy(['registrationToken' => $token])?->getId();

        if (!$userId) {
            return false;
        }

        $expectedUrl = sprintf('/user/%s/set-password', $userId);

        return $expectedUrl === $request->getPathInfo() && $request->isMethod('PUT');
    }

    private function requestHasToken(Request $request): bool
    {
        if (empty($request->getContent())) {
            return false;
        }

        $body = json_decode($request->getContent(), true);

        return isset($body['token']);
    }

    public function setBruteForceKey(string $bruteForceKey)
    {
        $this->bruteForceKey = $bruteForceKey;
    }
}
