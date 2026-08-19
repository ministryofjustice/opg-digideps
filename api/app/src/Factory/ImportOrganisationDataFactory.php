<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImportOrganisationDataFactory implements DataFactoryInterface
{
    private const string DOMAIN_REGEX = '/^(?:(?!-)(?:xn--|_)?[a-z0-9-]{0,61}[a-z0-9]\.)*(?:xn--)?(?:[a-z0-9][a-z0-9\-]{0,60}|[a-z0-9-]{1,30}\.[a-z]{2,})$/';

    /**
     * @var array<int, string> $errors
     */
    private array $errors;

    private string $blacklist;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        ParameterBagInterface $parameters
    ) {
        $this->errors = [];
        $unvalidated = $parameters->get('shared_email_domains');
        $blacklist = [];
        if (!empty($unvalidated) && is_array($unvalidated)) {
            foreach ($unvalidated as $domain) {
                if (is_string($domain) && preg_match(self::DOMAIN_REGEX, $domain)) {
                    $blacklist[] = "'{$domain}'";
                } else {
                    throw new \DomainException("Parameter 'shared_email_domains' must only contain valid domains");
                }
            }
        } else {
            throw new \DomainException("Parameter 'shared_email_domains' must be a non empty array");
        }

        $blacklist = implode(',', $blacklist);
        $this->blacklist = "({$blacklist})";
    }

    public function getName(): string
    {
        return 'ImportOrganisation';
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $created = $preLinked = $updated = $inserted = $postLinked = 0;
        if ($this->beginTransaction()) {
            try {
                $this->execute('DELETE FROM staging.sirius_organisation ssd WHERE TRUE');
                $created = $this->execute("
                    INSERT INTO staging.sirius_organisation (domain, name)
                    SELECT
                        o.domain,
                        (ARRAY_AGG(o.name))[1]
                    FROM (
                        SELECT
                            CASE
                                WHEN SUBSTRING(spi.deputy_email from '@(.*)$') IN {$this->blacklist} THEN spi.deputy_email
                                ELSE SUBSTRING(spi.deputy_email from '@(.*)$')
                            END AS domain,
                            spi.deputy_organisation AS name
                        FROM staging.pa_pro_ingest spi
                    ) o
                    -- FROM (
                    --     SELECT
                    --         substring(spi.deputy_email from '@(.*)$') AS domain,
                    --         spi.deputy_organisation AS name
                    --     FROM staging.pa_pro_ingest spi
                    -- ) o
                    -- WHERE o.domain NOT IN {$this->blacklist}
                    GROUP BY o.domain
                ");
                $preLinked = $this->execute('
                    UPDATE staging.sirius_organisation sso
                    SET local_id = o.id
                    FROM organisation o
                    WHERE o.email_identifier = sso.domain
                ');
                if (!$dryRun) {
                    $updated = $this->execute("
                        UPDATE organisation o
                        SET name = COALESCE(sso.name, o.name)
                        FROM staging.sirius_organisation sso
                        WHERE o.id = sso.local_id
                    ");
                    $inserted = $this->execute("
                        INSERT INTO organisation (
                            name, email_identifier, is_activated
                        )
                        SELECT
                            COALESCE(sso.name, 'Your Organisation'),
                            sso.domain,
                            TRUE
                        FROM staging.sirius_organisation sso
                        WHERE sso.local_id IS NULL
                    ");
                    $postLinked = $this->execute('
                        UPDATE staging.sirius_organisation sso
                        SET local_id = o.id
                        FROM organisation o
                        WHERE o.email_identifier = sso.domain
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
            "Created {$created} entries in sirius_organisation.",
            "Linked {$preLinked} existing entries in organisation.",
        ];
        if (!$dryRun) {
            $messages[] = "Updated {$updated} existing entries in organisation.";
            $messages[] = "Created {$inserted} entries in organisation.";
            $messages[] = " Linked {$postLinked} entries in organisation.";
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
