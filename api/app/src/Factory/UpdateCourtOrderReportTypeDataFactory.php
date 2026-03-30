<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;

final readonly class UpdateCourtOrderReportTypeDataFactory implements DataFactoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getName(): string
    {
        return 'UpdateCourtOrderReportType';
    }

    public function run(): DataFactoryResult
    {
        $messages = [];
        $errorMessages = [];

        try {
            $this->entityManager->flush();
            $this->entityManager->clear();

            $count = $this->entityManager->getConnection()->executeQuery('
                UPDATE deputy d
                SET deputy_type = ds.deputy_type
                FROM staging.deputyship ds
                WHERE d.deputy_uid = ds.deputy_uid
            ')->rowCount();
            $messages[] = "Updated {$count} court order entities.";
        } catch (\Throwable $throwable) {
            $errorMessages[] = $throwable->getMessage();
        }
        return new DataFactoryResult(['success' => $messages], ['error' => $errorMessages]);
    }
}
