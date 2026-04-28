<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

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
                UPDATE deputy d
                SET deputy_type = ds.deputy_type
                FROM staging.deputyship ds
                WHERE
                    d.deputy_uid = ds.deputy_uid
                    AND ds.deputy_type IS NOT NULL
            ')->rowCount();
            $messages[] = "Updated {$count} deputy entities.";
        } catch (\Throwable $throwable) {
            $errorMessages[] = $throwable->getMessage();
        }
        return new DataFactoryResult(['success' => $messages], ['error' => $errorMessages]);
    }
}
