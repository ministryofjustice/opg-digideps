<?php

namespace App\v2\Registration\Uploader;

use App\Entity\CourtOrder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CourtOrderUpdater
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function upload(array $courtOrderUids): void
    {
        $courtOrdersUpdated = 0;
        $errors = [];

        try {
            $this->em->beginTransaction();

            $courtOrders = $this->em->getRepository(CourtOrder::class)->findAll();

            foreach ($courtOrders as $existingCourtOrder) {
                if (!in_array($existingCourtOrder['court_order_uid'], $courtOrderUids)) {
                    $existingCourtOrder->setActive(false);
                    $this->em->persist($existingCourtOrder);

                    $message = sprintf('Court Order UID: %s made inactive', $existingCourtOrder['court_order_uid']);
                    $this->logger->info($message);
                    ++$courtOrdersUpdated;
                }
            }

            $this->commitTransactionToDatabase();

            $message = sprintf('Number of Court Orders made inactive: %s', $courtOrdersUpdated);
            $this->logger->info($message);
        } catch (\Throwable $e) {
            $message = sprintf('Failure in updating Court Order UID: %s - %s', $existingCourtOrder['court_order_uid'], $e->getMessage());
            $this->logger->error($message);
        }
    }

    private function commitTransactionToDatabase(): void
    {
        $this->em->flush();
        $this->em->commit();
        $this->em->clear();
    }
}
