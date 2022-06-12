<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Exception\InvalidRegistrationTokenException;
use App\Repository\UserRepository;
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
    public function __construct(private UserRepository $userRepository, private TokenStorageInterface $tokenStorage)
    {
    }

    public function supports(Request $request): ?bool
    {
        return '/auth/login' === $request->getPathInfo() &&
            $request->isMethod('POST') &&
            $this->requestHasToken($request);
    }

    public function authenticate(Request $request)
    {
        $content = json_decode($request->getContent(), true);
        $token = $content['token'];
        $user = $this->userRepository->findOneBy(['registrationToken' => $token]);

        if (!$user instanceof User) {
            throw new UserNotFoundException('User not found');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getEmail()),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->tokenStorage->setToken($token);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new InvalidRegistrationTokenException();
    }

    private function requestHasToken(Request $request): bool
    {
        if (empty($request->getContent())) {
            return false;
        }

        $body = json_decode($request->getContent(), true);

        return isset($body['token']);
    }
}
