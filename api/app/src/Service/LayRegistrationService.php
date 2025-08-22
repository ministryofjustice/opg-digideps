<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;

class LayRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientRepository $clientRepository,
        private readonly ReportService $reportService,
    ) {
    }

    /**
     * Find all clients with an entry in the pre_registration table but without a report.
     * Add a report to each of those clients using data from the pre_registration table.
     *
     * @return int number of reports which were added to clients
     */
    public function addMissingReports(int $batchSize = 50): int
    {
        $clientsWithoutAReport = $this->clientRepository->findClientsWithoutAReport();

        // for each of those clients, decide which type of report to add and create it
        $numItemsPersisted = 0;
        foreach ($clientsWithoutAReport as $client) {
            // work out which type(s) of report to create based on comparing/aggregating the pre-reg rows
            // for this client's case number
            $reportsToAdd = $this->reportService->createRequiredReports($client);

            foreach ($reportsToAdd as $reportToAdd) {
                $this->entityManager->persist($reportToAdd);

                ++$numItemsPersisted;

                if (0 === $numItemsPersisted % $batchSize) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $numItemsPersisted;
    }
}
