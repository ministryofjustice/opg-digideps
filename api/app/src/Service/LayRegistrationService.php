<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Factory\ReportFactory;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class LayRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientRepository $clientRepository,
        private readonly ReportFactory $reportFactory,
    ) {
    }

    /**
     * Find all clients with an entry in the pre_registration table but without a report.
     * Add a report to each of those clients using data from the pre_registration table.
     *
     * @return int number of reports which were added to clients
     */
    public function addMissingReports(): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        // find all reports which are missing
        $missingReports = $qb->select(
            'c.id AS clientId',
            'pr.typeOfReport',
            'pr.orderType',
            'pr.orderDate',
        )
            ->distinct()
            ->from(PreRegistration::class, 'pr')
            ->innerJoin(Client::class, 'c', Join::WITH, 'pr.caseNumber = c.caseNumber')
            ->leftJoin(Report::class, 'r', Join::WITH, 'c.id = r.client')
            ->where('r.id IS NULL')
            ->andWhere('c.archivedAt IS NULL')
            ->andWhere('c.deletedAt IS NULL')
            ->getQuery()
            ->getArrayResult();

        // add reports to clients without them
        $batchSize = 50;
        $numItemsPersisted = 0;

        foreach ($missingReports as $missingReport) {
            /** @var ?Client $client */
            $client = $this->clientRepository->find($missingReport['clientId']);

            // this shouldn't happen, as we just queried for the client IDs we are now looking up
            if (is_null($client)) {
                continue;
            }

            $report = $this->reportFactory->create(
                $client,
                $missingReport['typeOfReport'],
                $missingReport['orderType'],
                $missingReport['orderDate']
            );

            $this->entityManager->persist($report);

            ++$numItemsPersisted;

            if (0 === $numItemsPersisted % $batchSize) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $numItemsPersisted;
    }
}
