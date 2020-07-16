<?php

namespace AppBundle\Entity\Repository;

class ChecklistRepository extends AbstractEntityRepository
{
    public function getQueuedAndSetToInProgress(string $limit)
    {
        return [];
    }
}
