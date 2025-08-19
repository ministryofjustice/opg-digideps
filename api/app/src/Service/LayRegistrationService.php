<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class LayRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Find all clients with an entry in the pre_registration table but without a report.
     * Add a report to each of those clients using data from the pre_registration table.
     */
    public function addMissingReports(): void
    {
        $qb = $this->entityManager->createQueryBuilder();

        $clientsWithoutReports = $qb->select(
            'c.id AS clientId',
            'pr.typeOfReport',
            'pr.orderType',
            'pr.hybrid',
            'pr.orderDate',
        )
            ->distinct()
            ->from(PreRegistration::class, 'pr')
            ->innerJoin(Client::class, 'c', Join::WITH, 'pr.caseNumber = c.caseNumber')
            ->leftJoin(Report::class, 'r', Join::WITH, 'c.id = r.client')
            ->where('r.id IS NULL')
            ->getQuery()
            ->getArrayResult();

        error_log(print_r($clientsWithoutReports, true));
    }
}
