<?php

declare(strict_types=1);

namespace App\Factory\MissingReport;

use App\Entity\CourtOrder;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MissingReportFinder
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return \Generator<CourtOrder>
     */
    public function findCourtOrdersWithMissingReports(): \Generator
    {
        $repository = $this->entityManager->getRepository(CourtOrder::class);
        foreach (
            $this->entityManager->getConnection()->executeQuery("
            SELECT DISTINCT co.id
            FROM court_order co
            LEFT JOIN court_order_report cor
                ON co.id = cor.court_order_id
            LEFT JOIN report r
                ON r.id = cor.report_id
                AND COALESCE(r.submitted, 'f') = 'f'
            GROUP BY co.id
            HAVING COUNT(r.id) = 0
            ")->iterateColumn() as $courtOrderId
        ) {
            $order = $repository->find($courtOrderId);
            if ($order !== null) {
                yield $order;
            }
        }
    }
}
