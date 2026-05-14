<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use Doctrine\ORM\EntityManagerInterface;

class ImportClientDataFactory implements DataFactoryInterface
{
    /**
     * @var array<int, string> $errors
     */
    private array $errors;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->errors = [];
    }

    public function getName(): string
    {
        return 'ImportClient';
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $created = $preLinked = $updated = $inserted = $postLinked = 0;
        if ($this->beginTransaction()) {
            try {
                $this->execute('DELETE FROM staging.sirius_client ssd WHERE TRUE');
                $created = $this->execute("
                    INSERT INTO staging.sirius_client (
                        case_number,
                        client_first_name,
                        client_last_name,
                        client_date_of_birth,
                        client_address1,
                        client_address2,
                        client_address3,
                        client_address4,
                        client_address5,
                        client_post_code
                    )
                    SELECT
                        COALESCE(spi.case_number, sli.case_number),
                        (ARRAY_AGG(COALESCE(spi.client_first_name, sli.client_first_name)))[1],
                        (ARRAY_AGG(COALESCE(spi.client_last_name, sli.client_last_name)))[1],
                        (ARRAY_AGG(spi.client_date_of_birth))[1],
                        (ARRAY_AGG(COALESCE(spi.client_address1, sli.client_address1)))[1],
                        (ARRAY_AGG(COALESCE(spi.client_address2, sli.client_address2)))[1],
                        (ARRAY_AGG(COALESCE(spi.client_address3, sli.client_address3)))[1],
                        (ARRAY_AGG(COALESCE(spi.client_address4, sli.client_address4)))[1],
                        (ARRAY_AGG(COALESCE(spi.client_address5, sli.client_address5)))[1],
                        (ARRAY_AGG(COALESCE(spi.client_post_code, sli.client_post_code)))[1]
                    FROM staging.lay_ingest sli
                    FULL OUTER JOIN staging.pa_pro_ingest spi
                        ON sli.case_number = spi.case_number
                    GROUP BY spi.case_number, sli.case_number
                ");
                $preLinked = $this->execute('
                    UPDATE staging.sirius_client ssc
                    SET local_id = c.id
                    FROM client c
                    WHERE c.case_number = ssc.case_number
                ');
                if (!$dryRun) {
                    $inserted = $this->execute("
                        INSERT INTO client (
                            case_number,
                            firstname,
                            lastname,
                            address,
                            address2,
                            address3,
                            address4,
                            address5,
                            postcode,
                            created_at
                        )
                        SELECT
                            ssc.case_number,
                            ssc.client_first_name,
                            ssc.client_last_name,
                            ssc.client_address1,
                            ssc.client_address2,
                            ssc.client_address3,
                            ssc.client_address4,
                            ssc.client_address5,
                            ssc.client_post_code,
                            now()
                        FROM staging.sirius_client ssc
                        WHERE ssc.local_id IS NULL
                    ");
                    $postLinked = $this->execute('
                        UPDATE staging.sirius_client ssc
                        SET local_id = c.id
                        FROM client c
                        WHERE c.case_number = ssc.case_number
                    ');
                }
            } catch (\Throwable $e) {
                $this->errors[] = "Database error in {$this->getName()}: {$e->getMessage()}";
            }
        }
        if (!$this->endTransaction(count($this->errors) === 0)) {
            $this->errors[] = "Database error in {$this->getName()} while ending transaction.";
        }

        $messages = [
            "Created {$created} entries in sirius_client.",
            "Linked {$preLinked} existing entries in client.",
        ];
        if (!$dryRun) {
            $messages[] = "Updated {$updated} existing entries in deputy.";
            $messages[] = "Created {$inserted} entries in deputy.";
            $messages[] = " Linked {$postLinked} new entries in deputy.";
        }
        return new DataFactoryResult([
            'counts' => $messages,
            'errors' => $this->errors,
        ]);
    }

    private function beginTransaction(): bool
    {
        return $this->entityManager->getConnection()->beginTransaction();
    }

    private function execute(string $sql): int
    {
        return $this->entityManager->getConnection()->executeQuery($sql)->rowCount();
    }

    private function endTransaction(bool $success): bool
    {
        return $success ? $this->entityManager->getConnection()->commit() : $this->entityManager->getConnection()->rollBack();
    }
}
