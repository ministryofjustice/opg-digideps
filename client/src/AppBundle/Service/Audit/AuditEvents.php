<?php

namespace AppBundle\Service\Audit;

final class AuditEvents
{
    const CLIENT_DISCHARGED = 'CLIENT_DISCHARGED';
    const CLIENT_DISCHARGED_ADMIN_TRIGGER = 'ADMIN_BUTTON';
    const CLIENT_DISCHARGED_CSV_TRIGGER = 'CSV_UPLOAD';

    /**
     * @param string $trigger
     * @param string $caseNumber
     * @param string $dischargedBy
     * @return array
     */
    public function clientDischarged(string $trigger, string $caseNumber, string $dischargedBy): array
    {
        $event = [
            'trigger' => $trigger,
            'case_number' => $caseNumber,
            'discharged_by' => $dischargedBy
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
