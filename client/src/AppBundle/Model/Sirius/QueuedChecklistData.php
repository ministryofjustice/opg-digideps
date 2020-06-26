<?php declare(strict_types=1);

namespace AppBundle\Model\Sirius;

class QueuedChecklistData
{
    /** @var int */
    private $checklistId;

    /**
     * @return int
     */
    public function getChecklistId(): int
    {
        return $this->checklistId;
    }

    /**
     * @param int $checklistId
     * @return QueuedChecklistData
     */
    public function setChecklistId(int $checklistId): QueuedChecklistData
    {
        $this->checklistId = $checklistId;
        return $this;
    }
}
