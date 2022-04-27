<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Exception\UserWrongCredentialsException;
use App\Repository\UserRepository;
use Predis\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginRequestAuthenticator extends AbstractAuthenticator
{
    public function __construct(private UserRepository $userRepository, private Client $redis)
    {
    }

    public function supports(Request $request): ?bool
    {
        return '/auth/login' === $request->getPathInfo() && $request->isMethod('POST') && !empty($request->getContent());
    }

    public function authenticate(Request $request): Passport
    {
        $content = json_decode($request->getContent(), true);
        $email = $content['email'];
        $password = $content['password'];

        return new Passport(
            new UserBadge($email, function ($userEmail) {
                $user = $this->userRepository->findOneBy(['email' => $userEmail]);

                if ($user instanceof User) {
                    return $user;
                }

                throw new UserNotFoundException('User not found');
            }),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new UserWrongCredentialsException();
    }
}
