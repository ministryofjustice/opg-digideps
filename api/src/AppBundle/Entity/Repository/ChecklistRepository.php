<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\SynchronisableInterface;

class ChecklistRepository extends AbstractEntityRepository
{
    /**
     * @param int $limit
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportsIdsWithQueuedChecklistsAndSetChecklistsToInProgress(int $limit): array
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT r.id FROM AppBundle\Entity\Report\Report r JOIN r.checklist c JOIN r.reportSubmissions rs WHERE c.synchronisationStatus = ?1 and rs.uuid IS NOT NULL')
            ->setParameter(1, SynchronisableInterface::SYNC_STATUS_QUEUED)
            ->setMaxResults($limit);

        $checklists = $query->getArrayResult();

        if (count($checklists)) {
            $conn = $this->getEntityManager()->getConnection();

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
