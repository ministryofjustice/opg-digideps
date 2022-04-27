<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\UnauthorizedSessionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class SessionAuthenticator extends AbstractAuthenticator
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->getSession()->has('_security_secured_area')) {
            $token = $request->getSession()->get('_security_secured_area') ? unserialize($request->getSession()->get('_security_secured_area')) : '';

            return $token instanceof PostAuthenticationToken;
        }

        return false;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        /** @var PostAuthenticationToken $token */
        $token = unserialize($request->getSession()->get('_security_secured_area'));

        return new SelfValidatingPassport(
            new UserBadge($token->getUserIdentifier())
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new UnauthorizedSessionException($exception->getMessage());
    }
}
