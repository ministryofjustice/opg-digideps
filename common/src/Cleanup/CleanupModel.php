<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Cleanup;

final class CleanupModel implements \JsonSerializable
{
    public function __construct(
        public ?string $caseNumber,
        public bool $notDryRun,
    ) {
    }

    public function jsonSerialize(): array
    {
        return ['caseNumber' => $this->caseNumber, 'notDryRun' => $this->notDryRun];
    }
}
