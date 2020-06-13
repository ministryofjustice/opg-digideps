<?php

namespace AppBundle\Service\Audit;

final class AuditEvents
{
    const CLIENT_DISCHARGED = 'CLIENT_DISCHARGED';
    const CLIENT_DISCHARGED_ADMIN_TRIGGER = 'ADMIN_BUTTON';
    const CLIENT_DISCHARGED_CSV_TRIGGER = 'CSV_UPLOAD';

    /**
     * @param $trigger
     * @param $caseNumber
     * @return array
     */
    public function clientDischarged(string $trigger, string $caseNumber, string $dischargedBy = null): array
    {
        $event = [
            'event' => AuditEvents::CLIENT_DISCHARGED,
            'trigger' => $trigger,
            'case_number' => $caseNumber
        ];

        if (null !== $dischargedBy) {
            $event['discharged_by'] = $dischargedBy;
        }

        return $event;
    }
}
