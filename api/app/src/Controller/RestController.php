<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\ReportInterface;
use App\Entity\User;
use App\Exception\NotFound;
use App\Model\Hydrator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class RestController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     * @param array|int       $criteriaOrId
     *
     * @return T
     *
     * @throws NotFound
     */
    protected function findEntityBy(string $entityClass, $criteriaOrId, $errorMessage = null): object
    {
        $repo = $this->em->getRepository($entityClass);
        $entity = is_array($criteriaOrId) ? $repo->findOneBy($criteriaOrId) : $repo->find($criteriaOrId);

        if (!$entity) {
            throw new NotFound($errorMessage ?: $entityClass.' not found');
        }

        return $entity;
    }

    protected function hydrateEntityWithArrayData($object, array $data, array $keySetters): void
    {
        Hydrator::hydrateEntityWithArrayData($object, $data, $keySetters);
    }

    protected function denyAccessIfReportDoesNotBelongToUser(ReportInterface $report)
    {
        if (!$this->isGranted('edit', $report->getClient())) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($report->getClient()->getId())) {
                throw $this->createAccessDeniedException('Report does not belong to user');
            }
        }
    }

    protected function denyAccessIfNdrDoesNotBelongToUser(Ndr $ndr)
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
    protected function denyAccessIfClientDoesNotBelongToUser(Client $client)
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
     * @return DateTime|null
     *
     * @throws Exception
     */
    protected function convertDateStringToDateTime(string $date)
    {
        return empty($date) ? null : new DateTime($date);
    }

    protected function checkIfUserHasAccessViaDeputyUid(int $clientId): bool
    {
        $hasAccess = false;
        // Check if the user has access on other accounts based on deputy uid
        if (in_array('ROLE_LAY_DEPUTY', $this->getUser()->getRoles())) {
            $deputyUid = $this->getUser()->getDeputyUid();
            if ($deputyUid) {
                $deputyUidArray = $this->em->getRepository(User::class)->findDeputyUidsForClient($clientId);
                if (in_array($deputyUid, array_column($deputyUidArray, 'deputyUid'))) {
                    $hasAccess = true;
                }
            }
        }

        return $hasAccess;
    }
}
