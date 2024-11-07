<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report\Checklist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class ChecklistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Checklist::class);
    }

    public function getResubmittableErrorChecklistsAndSetToQueued(string $limit)
    {
        $resubmittableErrorChecklistsQuery = "
SELECT id as checklist_id
FROM checklist AS c
WHERE c.synchronisation_status='PERMANENT_ERROR'
AND
    (
        c.synchronisation_error LIKE '%500 Internal Server Error%'
    )
LIMIT $limit;";

        $conn = $this->getEntityManager()->getConnection();

        $docStmt = $conn->prepare($resubmittableErrorChecklistsQuery);
        $result = $docStmt->executeQuery();

        $checklists = [];
        // Get all queued checklists
        $results = $result->fetchAllAssociative();
        foreach ($results as $row) {
            $checklists[$row['checklist_id']] = [
                'checklist_id' => $row['checklist_id'],
            ];
        }
        if (count($checklists) > 0) {
            $this->setErrorChecklistsToQueued($checklists, $conn);

            return $checklists;
        }

        return [];
    }

    /**
     * @throws Exception
     */
    private function setErrorChecklistsToQueued(array $checklists, Connection $connection): void
    {
        if (count($checklists)) {
            // Set checklists to queued where they are re-submittable
            $ids = [];
            foreach ($checklists as $data) {
                $ids[] = $data['checklist_id'];
            }

            $idsString = implode(',', $ids);

            $updateStatusQuery = "
UPDATE checklist
SET synchronisation_status = 'QUEUED', synchronisation_error = null, synchronisation_time = null
WHERE id IN ($idsString)";
            $stmt = $connection->prepare($updateStatusQuery);

            $stmt->executeQuery();
        }
    }
}
