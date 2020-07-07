<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Model\Sirius\QueuedChecklistData;

class ChecklistSyncService
{
    /**
     * @param QueuedChecklistData $checklistData
     */
    public function sync(QueuedChecklistData $checklistData)
    {

    }

    /**
     * @return array
     */
    public function getSyncErrorSubmissionIds(): array
    {
        return [];
    }

    /**
     * @param array $ids
     */
    public function setSyncErrorSubmissionIds(array $ids): void
    {

    }

    /**
     * @return int
     */
    public function getChecklistsNotSyncedCount(): int
    {
        return 0;
    }

    /**
     * @param int $count
     */
    public function setChecklistsNotSyncedCount(int $count): void
    {

    }

    public function setChecklistsToPermanentError(): void
    {

    }

}
