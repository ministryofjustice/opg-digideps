<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

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

    public function run(bool $dryRun): DataFactoryResult
    {
        $messages = [];
        $errorMessages = [];

        try {
            if (!$dryRun) {
                $this->entityManager->flush();
                $this->entityManager->clear();

                $count = $this->entityManager->getConnection()->executeQuery("
                    UPDATE court_order co
                    SET order_report_type = CASE
                        WHEN co.order_type = 'pfa' OR co.order_kind = 'hybrid' THEN COALESCE(d.report_type, co.order_report_type)
                        WHEN co.order_type = 'hw' AND co.order_kind <> 'hybrid' THEN 'OPG104'
                        WHEN co.order_type = 'hw' AND co.order_kind = 'hybrid' THEN COALESCE(d_s.report_type, d.report_type, co.order_report_type)
                    END
                    FROM staging.deputyship d
                    LEFT JOIN court_order co_s
                        ON co_s.id = sibling_id
                    LEFT JOIN staging.deputyship d_s
                        ON co_s.court_order_uid = d_s.order_uid
                    WHERE
                        co.court_order_uid = d.order_uid
                ")->rowCount();
                $messages[] = "Updated {$count} court order entities.";
            }
        } catch (\Throwable $throwable) {
            $errorMessages[] = $throwable->getMessage();
        }
        return new DataFactoryResult(['success' => $messages], ['error' => $errorMessages]);
    }
}
