<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Cleanup;

final class CleanupModel
{
    public function __construct(
        public ?string $caseNumber,
        public bool $notDryRun,
        public bool $allowNotContinuous
    ) {
    }
}
