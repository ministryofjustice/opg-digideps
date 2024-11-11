<?php

declare(strict_types=1);

namespace App\Service\Client;

use App\Service\Time\DateTimeProvider;

final class LayCsvEvents
{
    public const PREREG_INGESTION_COMPLETED = 'PREREG_INGESTION_COMPLETED';
    public const LAY_MULTICLIENT_CREATION = 'MULTICLIENT_CREATION_COMPLETED';
    
    public function __construct(private readonly DateTimeProvider $dateTimeProvider)
    {
    }
    
    public function csvProcessingEvent(
        string $trigger,
        string $completionState,
        string $jobName,
        string $processedOutput
    ): array {
        
        $event = [
            'trigger' => $trigger,
            'job_name' => $jobName,
            'completion_state' => $completionState,
            'job_output' => $processedOutput,
            'completion_time' => $this->dateTimeProvider->getDateTime()->format(\DateTime::ATOM)
        ];
        
        return $event + $this->baseEvent(LayCsvEvents::PREREG_INGESTION_COMPLETED);
    }
    
    private function baseEvent(string $eventName): array
    {
        return [
            'event' => $eventName,
            'type' => 'lay_csv_processing'
        ];
    }
}
