<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use JMS\Serializer\Annotation\Exclude;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;

#[Exclude]
final readonly class CleanupProblem
{
    public function __construct(
        public Client $client,
        public ?Report $report,
        public string $problem
    ) {
    }

    public function toCsvLine(): string
    {
        return implode(',', [
            $this->client->getCaseNumber(),
            '',
            $this->report?->getId() ?? '',
            '',
            $this->report?->getStartDate()?->format('Y-m-d') ?? '',
            $this->report?->getEndDate()?->format('Y-m-d') ?? '',
            $this->problem,
            'problem'
        ]);
    }
}
