<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PreRegistrationCompletedEvent extends Event
{
    public const NAME = 'deputy.lay_csv_preregistartion_completed';
    
    public function __construct(
        private readonly string $trigger,
        private readonly string $jobName,
        private readonly string $completionState,
        private readonly string $processedOutput
    ) {
        
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function getJobName(): string
    {
        return $this->jobName;
    }

    public function getCompletionState(): string
    {
        return $this->completionState;
    }

    public function getProcessedOutput(): string
    {
        return $this->processedOutput;
    }
}
