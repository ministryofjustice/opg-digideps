<?php

namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\DateTimeProvider;
use DateTime;

final class AuditEvents
{
    const CLIENT_DISCHARGED = 'CLIENT_DISCHARGED';
    const CLIENT_DISCHARGED_ADMIN_TRIGGER = 'ADMIN_BUTTON';
    const CLIENT_DISCHARGED_CSV_TRIGGER = 'CSV_UPLOAD';

    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;

    public function __construct(DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @param string $trigger
     * @param string $caseNumber
     * @param string $dischargedBy
     * @param string $deputyName
     * @param DateTime|null $deputyshipStartDate
     * @return array
     * @throws \Exception
     */
    public function clientDischarged(
        string $trigger,
        string $caseNumber,
        string $dischargedBy,
        string $deputyName,
        ?DateTime $deputyshipStartDate
    ): array
    {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'discharged_by' => $dischargedBy,
            'deputy_name' => $deputyName,
            'discharged_on' => $this->dateTimeProvider->getDateTime()->format(DateTime::ATOM),
            'deputyship_start_date' => $deputyshipStartDate ? $deputyshipStartDate->format(DateTime::ATOM) : null,
        ];

        return $event + $this->baseEvent(AuditEvents::CLIENT_DISCHARGED);
    }

    /**
     * @param string $eventName
     * @return array
     */
    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'audit'
        ];
    }
}
