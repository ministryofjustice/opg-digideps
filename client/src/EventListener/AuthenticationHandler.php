<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());

        return new RedirectResponse($referer);
    }

    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        $request->getSession()->set('fromLogoutPage', 1);

        return new RedirectResponse('/login');
    }
}
