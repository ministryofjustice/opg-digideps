<?php

namespace App\v2\Registration\Uploader;

use App\Entity\CourtOrder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CourtOrderUpdater
{
    private ?int $currentCourtOrderUID = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<int> $courtOrderUids
     */
    public function upload(array $courtOrderUids): void
    {
        $courtOrdersUpdated = 0;

        try {
            $this->em->beginTransaction();

            $courtOrders = $this->em->getRepository(CourtOrder::class)->findAll();

            foreach ($courtOrders as $existingCourtOrder) {
                $this->currentCourtOrderUID = $existingCourtOrder->getCourtOrderUid();
                
                if (!in_array($existingCourtOrder->getCourtOrderUid(), $courtOrderUids)) {
                    $existingCourtOrder->setActive(false);
                    $this->em->persist($existingCourtOrder);

                    $message = sprintf('Court Order UID: %s made inactive', $existingCourtOrder->getCourtOrderUid());
                    $this->logger->info($message);
                    ++$courtOrdersUpdated;
                }
            }

            $this->commitTransactionToDatabase();

            $message = sprintf('Number of Court Orders made inactive: %s', $courtOrdersUpdated);
            $this->logger->info($message);
        } catch (\Throwable $e) {
            $message = sprintf('Failure in updating Court Order UID: %s - %s', $this->currentCourtOrderUID, $e->getMessage());
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
