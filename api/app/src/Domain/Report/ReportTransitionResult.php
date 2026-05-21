<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

final class ReportTransitionResult
{
    public function __construct(
        public array $messages = [],
        public array $errorMessages = [],
        public bool $transitioned = false
    ) {
    }

    public function hasError(): bool
    {
        return count($this->errorMessages) > 0;
    }
}
