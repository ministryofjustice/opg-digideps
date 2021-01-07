<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Exception\NotFound;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class RestController extends Controller
{
    /**
     * @param $entityClass string
     *
     * @return EntityRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->getDoctrine()->getManager()->getRepository($entityClass);
    }

    /**
     * @param string    $entityClass
     * @param array|int $criteriaOrId
     * @param string    $errorMessage
     *
     * @throws NotFound
     */
    protected function findEntityBy($entityClass, $criteriaOrId, $errorMessage = null)
    {
        $repo = $this->getRepository($entityClass);
        $entity = is_array($criteriaOrId) ? $repo->findOneBy($criteriaOrId) : $repo->find($criteriaOrId);

        if (!$entity) {
            throw new NotFound($errorMessage ?: $entityClass . ' not found');
        }

        return $entity;
    }

    /**
     * @param mixed $object
     * @param array $data
     * @param array $keySetters
     */
    protected function hydrateEntityWithArrayData($object, array $data, array $keySetters)
    {
        foreach ($keySetters as $k => $setter) {
            if (array_key_exists($k, $data)) {
                $object->$setter($data[$k]);
            }
        }
    }

    /**
     * @param EntityDir\ReportInterface $report
     */
    protected function denyAccessIfReportDoesNotBelongToUser(EntityDir\ReportInterface $report)
    {
        if (!$this->isGranted('edit', $report->getClient())) {
            throw $this->createAccessDeniedException('Report does not belong to user');
        }
    }

    /**
     * @param EntityDir\Ndr\Ndr $ndr
     */
    protected function denyAccessIfNdrDoesNotBelongToUser(EntityDir\Ndr\Ndr $ndr)
    {
        if (!$this->isGranted('edit', $ndr->getClient())) {
            throw $this->createAccessDeniedException('Ndr does not belong to user');
        }
    }

    /**
     * @param Client $client
     */
    protected function denyAccessIfClientDoesNotBelongToUser(EntityDir\Client $client)
    {
        if (!$this->isGranted('edit', $client)) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }
    }
}
