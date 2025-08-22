<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\StagingSelectedCandidate;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Fill in missing dd_user (proxying for deputy) <-> client (case) relationships by populating the deputy_case table.
 * This is necessary to allow deputies to access the client records of court orders they are associated with.
 */
class DeputyCaseService
{
    // Query to find records in the deputyship table which connect a deputy UID to a case number,
    // where there is no corresponding record in the deputy_case table;
    // ignore archived and deleted clients: we don't need to associate them with deputies.
    /** @var string */
    private const DEPUTY_CASE_CANDIDATES_QUERY = <<<SQL
        SELECT d.order_uid AS order_uid, c.id AS client_id, ddu.id AS user_id
        FROM staging.deputyship d
        INNER JOIN client c
        ON LOWER(d.case_number) = LOWER(c.case_number)
        INNER JOIN dd_user ddu
        ON ddu.deputy_uid::varchar = d.deputy_uid
        WHERE (c.id, ddu.id) NOT IN (SELECT client_id, deputy_id FROM deputy_case)
        AND c.archived_at IS NULL
        AND c.deleted_at IS NULL;
    SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return \Traversable<StagingSelectedCandidate>
     *
     * @throws DBALException
     */
    public function addMissingDeputyCaseAssociations(): \Traversable
    {
        $conn = $this->entityManager->getConnection();

        return $conn->executeQuery(self::DEPUTY_CASE_CANDIDATES_QUERY)->iterateAssociative();
    }
}
