<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/audit-log")
 */
class AuditLogController extends RestController
{

    /**
     * exampleof request
     * {"performed_by_user":{"id":1},"ip_address":192,"created_at":"2015-06-02T12:53:53+0100","action":"user_add","user_edited":{"id":6}}
     * 
     * 
     * @Route("")
     * @Method({"POST"})
     */
    public function addAction()
    {
        $data = $this->deserializeBodyContent();

        // assert mandatory params
        foreach (['performed_by_user', 'ip_address', 'created_at', 'action'] as $k) {
            if (!array_key_exists($k, $data)) {
                throw new \InvalidArgumentException("Missing parameter $k");
            }
        }
        if (!array_key_exists('id', $data['performed_by_user'])) {
            throw new \InvalidArgumentException("Missing parameter performed_by_user.id");
        }

        $auditLogEntry = new EntityDir\AuditLogEntry(
            $this->findEntityBy('User', $data['performed_by_user']['id']), // perfomed by
            $data['ip_address'], 
            new \DateTime($data['created_at']), 
            $data['action'],
            isset($data['user_edited']['id']) ? $this->findEntityBy('User', $data['user_edited']['id']) : null
        );

        $this->getEntityManager()->persist($auditLogEntry);
        $this->getEntityManager()->flush();

        return $auditLogEntry;
    }

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        $this->setJmsSerialiserGroup(['audit_log']);

        return $this->getRepository('AuditLogEntry')->findBy([], ['id'=>'DESC']);
    }

}