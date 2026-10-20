<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use Doctrine\ORM\EntityManagerInterface;

final readonly class FixClientOrganisationFactory implements DataFactoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getName(): string
    {
        return 'FixClientOrganisation';
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $messages = [];
        $errors = [];
        try {
            if (!$dryRun) {
                $count = $this->entityManager->getConnection()->executeStatement("
                    UPDATE client c_u
                    SET organisation_id = cos.organisation_ids[1]
                    FROM (
                        SELECT
                            c.id,
                            ARRAY_AGG(DISTINCT o.id ORDER BY o.id) FILTER(WHERE o.id IS NOT NULL)  AS organisation_ids
                        FROM client c
                        JOIN court_order co
                            ON co.client_id = c.id
                            AND co.status = 'ACTIVE'
                        LEFT JOIN court_order_deputy cod
                            ON cod.court_order_id = co.id
                            AND cod.is_active
                        LEFT JOIN deputy d
                            ON cod.deputy_id = d.id
                            AND d.organisation_id IS NOT NULL
                        LEFT JOIN organisation o
                            ON o.id = d.organisation_id
                            AND o.is_activated
                            AND o.deleted_at IS NULL
                        GROUP BY c.id
                    ) cos
                    WHERE
                        cos.id = c_u.id
                        AND cos.organisation_ids IS NOT NULL
                        AND NOT (COALESCE(c_u.organisation_id, 0) = ANY(cos.organisation_ids))
                    ;
                ");
                $messages[] = "Affected organisation id of {$count}  client entities";
            }
        } catch (\Throwable $throwable) {
            $errors[] = $throwable->getMessage();
        }
        return new DataFactoryResult(['messages' => $messages], ['errors' => $errors]);
    }
}
