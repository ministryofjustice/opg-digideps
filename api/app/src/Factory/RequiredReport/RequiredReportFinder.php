<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory\RequiredReport;

use OPG\Digideps\Backend\Entity\CourtOrder;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RequiredReportFinder
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return \Generator<CourtOrder>
     */
    public function findCourtOrdersWithoutRequiredReports(): \Generator
    {
        $repository = $this->entityManager->getRepository(CourtOrder::class);
        foreach (
            $this->entityManager->getConnection()->executeQuery("
            SELECT DISTINCT co.id
            FROM court_order co
            LEFT JOIN report r
                ON (co.id = r.pfa_court_order_id AND co.order_type = 'pfa'
                OR co.id = r.hw_court_order_id AND co.order_type = 'hw')
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
