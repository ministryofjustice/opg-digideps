<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/audit-log")
 */
class AuditLogController extends RestController
{
    /**
     * exampleof request
     * {"performed_by_user":{"id":1},"ip_address":192,"created_at":"2015-06-02T12:53:53+0100","action":"user_add","user_edited":{"id":6}}.
     *
     *
     * @Route("")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $data = $this->deserializeBodyContent($request, [
            'performed_by_user' => 'mustExist',
            'ip_address' => 'mustExist',
            'created_at' => 'mustExist',
            'action' => 'mustExist',
        ]);

        if (!array_key_exists('id', $data['performed_by_user'])) {
            throw new \InvalidArgumentException('Missing parameter performed_by_user.id');
        }

        $auditLogEntry = new EntityDir\AuditLogEntry();
        $auditLogEntry
            ->setPerformedByUser($this->findEntityBy(EntityDir\User::class, $data['performed_by_user']['id']))
            ->setIpAddress($data['ip_address'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setAction($data['action']);

        if (isset($data['user_edited']['id'])) {
            $auditLogEntry->setUserEdited($this->findEntityBy(EntityDir\User::class, $data['user_edited']['id']));
        }

        $this->persistAndFlush($auditLogEntry);

        return $auditLogEntry;
    }

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $this->setJmsSerialiserGroups(['audit_log']);

        return $this->getRepository(EntityDir\AuditLogEntry::class)->findBy([], ['id' => 'DESC']);
    }
}
