<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\AuditLogEntry;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use AppBundle\Service\Client\RestClient;

class AuditLogger
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var Request
     */
    private $request;

    public function __construct(RestClient $restClient, SecurityContextInterface $securityContext, Container $container)
    {
        $this->restClient = $restClient;
        $this->securityContext = $securityContext;
        $this->request = $container->get('request');
    }

    public function log($action, User $userEdited = null)
    {
        //only log admin actions
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            return;
        }

        $performedByUser = $this->securityContext->getToken()->getUser();

        $entry = new AuditLogEntry();
        $entry
            ->setPerformedByUser($performedByUser)
            ->setIpAddress($this->request->getClientIp())
            ->setAction($action)
            ->setUserEdited($userEdited);

        $ret = $this->restClient->post('audit-log', $entry, [
            'audit_log_save',
         ]);

        return $ret;
    }
}
