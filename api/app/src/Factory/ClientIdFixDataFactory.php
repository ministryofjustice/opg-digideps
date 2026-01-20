<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class ClientIdFixDataFactory implements DataFactoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function getName(): string
    {
        return 'ClientIdFix';
    }

    /**
     * @throws Exception
     */
    public function run(): DataFactoryResult
    {
        // while this SQL only queries for court orders where the client ID needs to be updated,
        // we will (for now) also update reports associated with those clients
        $sql = <<<'SQL'
        SELECT DISTINCT old_client_id, new_client_id
        FROM (
          SELECT
              c1.id AS old_client_id,
              c2.id AS new_client_id,

              -- deduplicate new client rows
              ROW_NUMBER() OVER (PARTITION BY c2.case_number ORDER BY c2.created_at DESC) AS row_number
          FROM court_order co
          INNER JOIN client c1 ON co.client_id = c1.id
          INNER JOIN client c2 ON c1.case_number = c2.case_number
          WHERE co.status = 'ACTIVE'

          -- inactive client
          AND (c1.deleted_at IS NOT NULL OR c1.archived_at IS NOT NULL)

          -- active client
          AND (c2.deleted_at IS NULL AND c2.archived_at IS NULL)
        ) client_id_fixes
        WHERE client_id_fixes.row_number = 1;
        SQL;

        // find client IDs needing update
        $result = $this->em->getConnection()->executeQuery($sql)->fetchAllAssociative();

        foreach ($result as $row) {
            $oldClientId = $row['old_client_id'];
            $newClientId = $row['new_client_id'];

            // update court orders
            $this->em->getConnection()->executeStatement(
                'UPDATE court_order SET client_id = ? WHERE client_id = ?',
                [$newClientId, $oldClientId],
            );

            // update reports
            $this->em->getConnection()->executeStatement(
                'UPDATE report SET client_id = ? WHERE client_id = ?',
                [$newClientId, $oldClientId],
            );
        }

        return new DataFactoryResult(messages: ['Success' => ['Client IDs patched successfully']]);
    }
}
