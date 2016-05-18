<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationHandler implements AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $referer = $request->headers->get('referer');
        $request->getSession()->setFlash('error', $exception->getMessage());

        return new RedirectResponse($referer);
    }

    public function onLogoutSuccess(Request $request)
    {
        $request->getSession()->set('fromLogoutPage', 1);

        return new RedirectResponse('/login');
    }
}
