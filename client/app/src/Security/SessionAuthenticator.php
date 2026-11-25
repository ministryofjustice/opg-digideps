<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Security;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Exception\UnauthorizedSessionException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class SessionAuthenticator extends AbstractAuthenticator
{
    private const array NON_ACCEPTED_ALLOW_LIST = ['/terms', '/cookies' , '/privacy', '/accessibility', '/logout'];

    public function __construct(private readonly RouterInterface $router)
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
        $user = $token->getUser();

        if (!($user instanceof User) || $user->getAgreeTermsUse() || $user->hasAdminRole()) {
            return null; // agreement to t&c given or unneeded
        }

        if ($request->getMethod() !== 'GET') {
            return null; // agreement to t&c not given but let's not lose data
        }

        $page = $request->getPathInfo();
        $route = $this->router->generate('account_accept', ['page' => $request->getPathInfo()]);

        if (str_starts_with($route, $page) || in_array($page, self::NON_ACCEPTED_ALLOW_LIST)) {
            return null; // agreement to t&c not required for public pages (nor for the accept page)
        }

        // user still needs to agree to t&c
        return new RedirectResponse($route);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new UnauthorizedSessionException($exception->getMessage());
    }
}
