<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\AuditLogEntry;
use AppBundle\Service\AuditLogger;
use AppBundle\Service\Client\RestClient;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    /**
     * @var SecurityContext
     */
    private $security;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var AuditLogger
     */
    private $auditLogger;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(SecurityContext $security, RestClient $restClient, Router $router, AuditLogger $auditLogger)
    {
        $this->security = $security;
        $this->restClient = $restClient;
        $this->router = $router;
        $this->auditLogger = $auditLogger;
    }

    public function onLogoutSuccess(Request $request)
    {
        $this->auditLogger->log(AuditLogEntry::ACTION_LOGOUT);

        $this->restClient->logout();

        $request->getSession()->set('loggedOutFrom', 'logoutPage');

        $response = new RedirectResponse($this->router->generate('login'));

        return $response;
    }
}
