<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use Doctrine\ORM\EntityManagerInterface;

class ImportDeputyDataFactory implements DataFactoryInterface
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
        return 'ImportDeputy';
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $created = $preLinked = $updated = $inserted = $postLinked = 0;
        if ($this->beginTransaction()) {
            try {
                $this->execute('DELETE FROM staging.sirius_deputy ssd WHERE TRUE');
                $created = $this->execute("
                    INSERT INTO staging.sirius_deputy (
                        deputy_uid,
                        deputy_type,
                        deputy_email,
                        deputy_organisation,
                        deputy_first_name,
                        deputy_last_name,
                        deputy_address1,
                        deputy_address2,
                        deputy_address3,
                        deputy_address4,
                        deputy_address5,
                        deputy_post_code
                    )
                    SELECT
                        COALESCE(spi.deputy_uid, sli.deputy_uid),
                        (ARRAY_AGG(COALESCE(spi.deputy_type, 'LAY')))[1],
                        (ARRAY_AGG(spi.deputy_email))[1],
                        (ARRAY_AGG(spi.deputy_organisation))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_first_name, sli.deputy_first_name)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_last_name, sli.deputy_last_name)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_address1, sli.deputy_address1)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_address2, sli.deputy_address2)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_address3, sli.deputy_address3)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_address4, sli.deputy_address4)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_address5, sli.deputy_address5)))[1],
                        (ARRAY_AGG(COALESCE(spi.deputy_post_code, sli.deputy_post_code)))[1]
                    FROM staging.lay_ingest sli
                    FULL OUTER JOIN staging.pa_pro_ingest spi
                        ON sli.deputy_uid = spi.deputy_uid
                    GROUP BY spi.deputy_uid, sli.deputy_uid
                ");
                $preLinked = $this->execute('
                    UPDATE staging.sirius_deputy ssd
                    SET local_id = d.id
                    FROM deputy d
                    WHERE d.deputy_uid = ssd.deputy_uid
                ');
                if (!$dryRun) {
                    $updated = $this->execute("
                        UPDATE deputy d
                        SET
                            email1 = COALESCE(ssd.deputy_email, d.email1),
                            deputy_type = COALESCE(ssd.deputy_type, d.deputy_type),
                            organisation_id = CASE
                                WHEN COALESCE(ssd.deputy_type, d.deputy_type) = 'LAY' THEN NULL
                                ELSE COALESCE(o.id, d.organisation_id)
                            END,
                            user_id = COALESCE(u.id, d.user_id)
                        FROM staging.sirius_deputy ssd
                        LEFT JOIN organisation o
                            ON ssd.deputy_email LIKE CONCAT('%@', o.email_identifier) OR ssd.deputy_email = o.email_identifier
                        LEFT JOIN dd_user u
                            ON u.deputy_uid = ssd.deputy_uid::BIGINT
                            AND u.is_primary IS true
                        WHERE d.id = ssd.local_id
                    ");
                    $inserted = $this->execute("
                        INSERT INTO deputy (
                            firstname,
                            lastname,
                            email1,
                            address1,
                            address2,
                            address3,
                            address4,
                            address5,
                            address_postcode,
                            deputy_uid,
                            deputy_type,
                            organisation_id,
                            user_id,
                            created_at
                        )
                        SELECT
                            ssd.deputy_first_name,
                            ssd.deputy_last_name,
                            ssd.deputy_email,
                            ssd.deputy_address1,
                            ssd.deputy_address2,
                            ssd.deputy_address3,
                            ssd.deputy_address4,
                            ssd.deputy_address5,
                            ssd.deputy_post_code,
                            ssd.deputy_uid,
                            ssd.deputy_type,
                            o.id,
                            u.id,
                            now()
                        FROM staging.sirius_deputy ssd
                        LEFT JOIN organisation o
                            ON ssd.deputy_email LIKE CONCAT('%@', o.email_identifier)
                        LEFT JOIN dd_user u
                            ON u.deputy_uid = ssd.deputy_uid::BIGINT
                        WHERE ssd.local_id IS NULL
                    ");
                    $postLinked = $this->execute('
                        UPDATE staging.sirius_deputy ssd
                        SET local_id = d.id
                        FROM deputy d
                        WHERE d.deputy_uid = ssd.deputy_uid
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
            "Created {$created} entries in sirius_deputy.",
            "Linked {$preLinked} existing entries in deputy.",
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
