<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

final readonly class ReportDescriptor
{
    public \DateTimeImmutable $endDate;
    public \DateTimeImmutable $dueDate;

    /**
     * @param array<string> $supportingDocumentsWithoutS3Objects
     */
    public function __construct(
        public \DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate = null,
        ?\DateTimeImmutable $dueDate = null,
        public ?\DateTimeImmutable $submitDate = null,
        public array $supportingDocumentsWithoutS3Objects = [],
    ) {
        $this->endDate = $endDate ?? $this->startDate->add(new \DateInterval('P12M'))->sub(new \DateInterval('P1D'));
        $this->dueDate = $dueDate ?? $this->endDate->add(new \DateInterval('P1M'));
    }
}
