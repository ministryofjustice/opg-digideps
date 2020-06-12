<?php

namespace AppBundle\Service\Audit;

final class AuditEvents
{
    const CLIENT_DISCHARGED = 'CLIENT_DISCHARGED';

    /**
     * @param $trigger
     * @param $caseNumber
     * @return array
     */
    public function clientDischarged($trigger, $caseNumber): array
    {
        return [
            'event' => AuditEvents::CLIENT_DISCHARGED,
            'trigger' => $trigger,
            'case_number' => $caseNumber
        ];
    }
}
