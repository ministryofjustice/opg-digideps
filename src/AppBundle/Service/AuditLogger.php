<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Service\ApiClient;
use AppBundle\Entity\AuditLogEntry;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;

class AuditLogger
{
    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var SecurityContextInterface 
     */
    protected $securityContext;

    /**
     * @var Request 
     */
    protected $request;


    public function __construct(ApiClient $apiClient, SecurityContextInterface $securityContext, Container $container)
    {
        $this->apiClient = $apiClient;
        $this->securityContext = $securityContext;
        $this->request = $container->get('request');
    }

    public function log($action, User $userEdited = null)
    {
        //only log admin actions
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            return;
        }
        
        $performedBy = $this->securityContext->getToken()->getUser();

        $entry = new AuditLogEntry($performedBy, $this->request->getClientIp(), new \DateTime(), $action);
        $entry->setUserEdited($userEdited);

        $ret = $this->apiClient->postC('audit-log', $entry, [
            'deserialise_group' => 'audit_log_save'
         ]);

        return $ret;
    }

}