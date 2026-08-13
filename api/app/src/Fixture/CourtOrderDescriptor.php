<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;

final readonly class CourtOrderDescriptor
{
    public ReportList $reportList;
    public function __construct(
        public DeputySet $deputySet,
        public CourtOrderReportType $reportType = CourtOrderReportType::OPG103,
        public ?\DateTimeImmutable $madeDate = null,
        public bool $active = true,
        public bool $single = true,
        public ?DeputySet $siblingDeputySet = null,
        ?ReportList $reportList = null,
    ) {
        if ($this->reportType === CourtOrderReportType::OPG104 && !$this->single) {
            throw new \DomainException('On a non single court order please specify 102 or 103. 104 is implied.');
        } elseif ($this->single && $this->siblingDeputySet) {
            throw new \DomainException('A single order must have a single set of deputies.');
        }
        $this->reportList = $reportList ?? ReportList::oneUnsubmittedReport($this->madeDate);
    }
}
