<?php

declare(strict_types=1);

namespace App\Service\Audit;

use App\Service\Time\DateTimeProvider;

final class AuditEvents
{
    public const EVENT_CLIENT_ARCHIVED = 'CLIENT_ARCHIVED';

    public const TRIGGER_USER_ARCHIVED_CLIENT = 'USER_ARCHIVED_CLIENT';

    public const USER_DELETED_AUTOMATION = 'USER_DELETED_AUTOMATION';

    public function __construct(private readonly DateTimeProvider $dateTimeProvider)
    {
    }

    /**
     * @throws \Exception
     */
    public function clientArchived(
        string $trigger,
        string $caseNumber,
        ?\DateTime $deputyshipStartDate,
        string $archivedBy,
    ): array {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'archived_by' => $archivedBy,
            'deputyship_start_date' => $deputyshipStartDate?->format(\DateTime::ATOM),
            'archived_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::EVENT_CLIENT_ARCHIVED);
    }

    public function userAccountAutomatedDeletion(
        string $trigger,
        int $id,
        string $email,
    ) {
        $event = [
            'trigger' => $trigger,
            'id' => $id,
            'email_address' => $email,
            'message' => 'Deletion due to two year retention policy',
            'deleted_on' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM),
        ];

        return $event + $this->baseEvent(AuditEvents::USER_DELETED_AUTOMATION);
    }

    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'audit',
        ];
    }
}
