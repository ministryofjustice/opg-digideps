<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;

final readonly class UpdateDeputyTypeDataFactory implements DataFactoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getName(): string
    {
        return 'UpdateDeputyType';
    }

    public function run(): DataFactoryResult
    {
        $messages = [];
        $errorMessages = [];

        try {
            $this->entityManager->flush();
            $this->entityManager->clear();

            $count = $this->entityManager->getConnection()->executeQuery('
                UPDATE court_order co
                SET order_report_type = d.report_type
                FROM staging.deputyship d
                WHERE co.court_order_uid = d.order_uid
            ')->rowCount();
            $messages[] = "Updated {$count} deputy entities.";
        } catch (\Throwable $throwable) {
            $errorMessages[] = $throwable->getMessage();
        }
        return new DataFactoryResult(['success' => $messages], ['error' => $errorMessages]);
    }
}
