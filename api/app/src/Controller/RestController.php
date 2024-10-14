<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Exception\NotFound;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class RestController extends AbstractController
{
    /**
     * @param $entityClass string
     */
    protected function getRepository(string $entityClass): ObjectRepository
    {
        return $this->getDoctrine()->getManager()->getRepository($entityClass);
    }

    /**
     * @param array|int $criteriaOrId
     *
     * @throws NotFound
     */
    protected function findEntityBy(string $entityClass, $criteriaOrId, $errorMessage = null): object
    {
        $repo = $this->getRepository($entityClass);
        $entity = is_array($criteriaOrId) ? $repo->findOneBy($criteriaOrId) : $repo->find($criteriaOrId);

        if (!$entity) {
            throw new NotFound($errorMessage ?: $entityClass.' not found');
        }

        return $entity;
    }

    protected function hydrateEntityWithArrayData($object, array $data, array $keySetters)
    {
        foreach ($keySetters as $k => $setter) {
            if (array_key_exists($k, $data)) {
                $object->$setter($data[$k]);
            }
        }
    }

    protected function denyAccessIfReportDoesNotBelongToUser(EntityDir\ReportInterface $report)
    {
        if (!$this->isGranted('edit', $report->getClient())) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($report->getClient()->getId())) {
                throw $this->createAccessDeniedException('Report does not belong to user');
            }
        }
    }

    protected function denyAccessIfNdrDoesNotBelongToUser(EntityDir\Ndr\Ndr $ndr)
    {
        if (!$this->isGranted('edit', $ndr->getClient())) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($ndr->getClient()->getId())) {
                throw $this->createAccessDeniedException('NDR does not belong to user');
            }
        }
    }

    /**
     * @param Client $client
     */
    protected function denyAccessIfClientDoesNotBelongToUser(EntityDir\Client $client)
    {
        if (!$this->isGranted('edit', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }
    }

    /**
     * @param array $date
     *
     * @return \DateTime|null
     *
     * @throws \Exception
     */
    protected function convertDateStringToDateTime(string $date)
    {
        return empty($date) ? null : new \DateTime($date);
    }

    protected function checkIfUserHasAccessViaDeputyUid(int $clientId): bool
    {
        $hasAccess = false;
        // Check if the user has access on other accounts based on deputy uid
        if (in_array('ROLE_LAY_DEPUTY', $this->getUser()->getRoles())) {
            $deputyUid = $this->getUser()->getDeputyUid();
            if ($deputyUid) {
                $deputyUidArray = $this->getDoctrine()->getManager()->getRepository(EntityDir\User::class)->findDeputyUidsForClient($clientId);
                if (in_array($deputyUid, array_column($deputyUidArray, 'deputyUid'))) {
                    $hasAccess = true;
                }
            }
        }

        return $hasAccess;
    }
}
