<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Service\RequestIdLoggerProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route(path: '/application-error', name: 'error_page')]
    public function errorResponse(Request $request): Response
    {
        $user = $this->getUser();

        $emailTranslation = match($user?->getRoles()[0]) {
            User::ROLE_PA_NAMED, User::ROLE_PA_ADMIN, User::ROLE_PA_TEAM_MEMBER =>
                'paSupportEmail',
            User::ROLE_PROF_NAMED, User::ROLE_PROF_ADMIN, User::ROLE_PROF_TEAM_MEMBER =>
                'profSupportEmail',
            User::ROLE_LAY_DEPUTY =>
                'layDeputySupportEmail',
            default =>
                'generalSupportEmail'
        };

        return $this->render('@App/Index/Errors/application-error.html.twig', [
            'emailTranslation' => $emailTranslation,
            'sessionId' => RequestIdLoggerProcessor::getSessionSafeIdFromContainer($request),
            'requestId' => RequestIdLoggerProcessor::getRequestIdFromContainer($request),
        ]);
    }

    #[Route(path: '/access-denied', name: 'access_denied')]
    public function accessDenied(): Response
    {
        return new Response(
            $this->renderView('@App/Index/Errors/access-denied.html.twig'),
            403  // return code
        );
    }

    #[Route(path: '/error-503', name: 'error-503')]
    public function error503(Request $request): ?Response
    {
        return $this->render('@App/Index/Errors/error-503.html.twig', [
            'request' => $request,
        ]);
    }
}
