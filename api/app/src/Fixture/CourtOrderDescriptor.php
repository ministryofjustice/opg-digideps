<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;

final readonly class CourtOrderDescriptor
{
    public function __construct(
        public DeputySet $deputySet,
        public CourtOrderReportType $reportType = CourtOrderReportType::OPG103,
        public int $submittedReports = 0,
        public bool $active = true,
        public bool $single = true,
        public bool $noReports = false,
        public ?DeputySet $siblingDeputySet = null,
    ) {
        if ($this->reportType === CourtOrderReportType::OPG104 && !$this->single) {
            throw new \DomainException('On a non single court order please specify 102 or 103. 104 is implied.');
        } elseif ($this->submittedReports < 0 || $this->submittedReports > 24) {
            throw new \DomainException('The number of submitted reports should be between 0 and 24.');
        } elseif ($this->single && $this->siblingDeputySet) {
            throw new \DomainException('A single order must have a single set of deputies.');
        }
    }
}
