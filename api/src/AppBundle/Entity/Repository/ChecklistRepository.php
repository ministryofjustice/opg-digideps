<?php

namespace AppBundle\Entity\Repository;

class ChecklistRepository extends AbstractEntityRepository
{
    /**
     * @param int $limit
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getQueuedAndSetToInProgress(int $limit): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $query = $conn->executeQuery(
            "select c.*, rc.submitted_by as rc_submitted_by, rc.answers, rc.decision from checklist c inner join report r on r.id = c.report_id left join review_checklist rc on rc.report_id = r.id where c.synchronisation_status = 'QUEUED' limit $limit",
        );

        $checklists = $query->fetchAll();

        if (count($checklists)) {

            $ids = array_map(function($checklist) {
                return $checklist['id'];
            }, $checklists);

            $idsString = implode(",", $ids);
            $queryString = "UPDATE checklist SET synchronisation_status = 'IN_PROGRESS' WHERE id IN ($idsString)";
            $query = $conn->prepare($queryString);
            $query->execute();
        }

        return $checklists;
    }
}
