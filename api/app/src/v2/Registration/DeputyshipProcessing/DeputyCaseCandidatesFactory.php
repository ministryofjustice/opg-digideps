<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\Factory\StagingSelectedCandidateFactory;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Fill in missing dd_user (proxying for deputy) <-> client (case) relationships by populating the deputy_case table.
 * This is necessary to allow deputies to access the client records of court orders they are associated with.
 */
class DeputyCaseCandidatesFactory
{
    /** @var string */
    private const DEPUTY_CASE_CANDIDATES_QUERY = <<<SQL
        SELECT d.order_uid AS order_uid, c.id AS client_id, ddu.id AS user_id
        FROM staging.deputyship d
        INNER JOIN client c
        ON LOWER(d.case_number) = LOWER(c.case_number)
        INNER JOIN dd_user ddu
        ON ddu.deputy_uid::varchar = d.deputy_uid
        WHERE (c.id, ddu.id) NOT IN (SELECT client_id, deputy_id FROM deputy_case);
    SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StagingSelectedCandidateFactory $candidateFactory,
    ) {
    }

    // Query to find records in the deputyship table which connect a deputy UID to a case number,
    // where there is no corresponding record in the deputy_case table.
    /**
     * @return \Traversable<StagingSelectedCandidate>
     *
     * @throws DBALException
     */
    public function create(): \Traversable
    {
        $conn = $this->entityManager->getConnection();

        $results = $conn->executeQuery(self::DEPUTY_CASE_CANDIDATES_QUERY)->iterateAssociative();

        foreach ($results as $row) {
            yield $this->candidateFactory->createDeputyCaseCandidate(
                $row['order_uid'], $row['user_id'], $row['client_id']
            );
        }
    }
}
