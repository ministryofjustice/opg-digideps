<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
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
    // Also ignore inactive and non-primary users.
    /** @var string */
    private const DEPUTY_CASE_CANDIDATES_QUERY = <<<SQL
        SELECT DISTINCT c.id AS client_id, ddu.id AS user_id
        FROM pre_registration p
        INNER JOIN client c
        ON LOWER(p.client_case_number) = LOWER(c.case_number)
        INNER JOIN dd_user ddu
        ON ddu.deputy_uid::varchar = p.deputy_uid
        LEFT JOIN deputy_case dc
        ON dc.client_id = c.id AND dc.user_id = ddu.id
        WHERE dc.client_id IS NULL
        AND dc.user_id IS NULL
        AND c.archived_at IS NULL
        AND c.deleted_at IS NULL
        AND ddu.is_primary IS true
        AND ddu.active IS true;
    SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientRepository $clientRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function addMissingDeputyCaseAssociations(int $batchSize = 50): int
    {
        $conn = $this->entityManager->getConnection();

        $missingAssociations = $conn->executeQuery(self::DEPUTY_CASE_CANDIDATES_QUERY)->iterateAssociative();

        $numAdded = 0;
        foreach ($missingAssociations as $missingAssociation) {
            // fetch the user and the client records
            /** @var ?User $user */
            $user = $this->userRepository->find($missingAssociation['user_id']);

            /** @var ?Client $client */
            $client = $this->clientRepository->find($missingAssociation['client_id']);

            // we ignore null values for $user and $client, as we are querying for objects we just looked up;
            // if either is null since we did the query, we just won't create an association
            if (is_null($user) || is_null($client)) {
                continue;
            }

            // associate them
            $client->addUser($user);
            $this->entityManager->persist($user);
            $this->entityManager->persist($client);

            ++$numAdded;

            if (0 === $numAdded % $batchSize) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $numAdded;
    }
}
