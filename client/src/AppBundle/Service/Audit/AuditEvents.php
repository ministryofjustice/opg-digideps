<?php

namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\ClockInterface;
use DateTime;

final class AuditEvents
{
    const CLIENT_DISCHARGED = 'CLIENT_DISCHARGED';
    const CLIENT_DISCHARGED_ADMIN_TRIGGER = 'ADMIN_BUTTON';
    const CLIENT_DISCHARGED_CSV_TRIGGER = 'CSV_UPLOAD';

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @param ClockInterface $clock
     */
    public function __construct(ClockInterface $clock)
    {
        $this->clock = $clock;
    }

    /**
     * @param string $trigger
     * @param string $caseNumber
     * @param string $dischargedBy
     * @return array
     */
    public function clientDischarged(
        string $trigger,
        string $caseNumber,
        string $dischargedBy,
        string $deputyName,
        DateTime $deputyshipStartDate
    ): array
    {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'discharged_by' => $dischargedBy,
            'deputy_name' => $deputyName,
            'discharged_on' => $this->clock->now(new DateTime())->format(DateTime::ATOM),
            'deputyship_start_date' => $deputyshipStartDate->format(DateTime::ATOM),
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
