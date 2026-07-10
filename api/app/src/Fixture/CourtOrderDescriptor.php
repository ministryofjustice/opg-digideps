<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;

final readonly class CourtOrderDescriptor
{
    /**
     * @param bool $latestReportReadyToSubmit If true, the latest report for the court order is filled in so that
     * it is in a submittable state. However, the made date must be one year before today, otherwise that will
     * block the submission, regardless of the state of the form.
     */
    public function __construct(
        public DeputySet $deputySet,
        public CourtOrderReportType $reportType = CourtOrderReportType::OPG103,
        public int $submittedReports = 0,
        public bool $active = true,
        public bool $single = true,
        public bool $noReports = false,
        public ?DeputySet $siblingDeputySet = null,
        public bool $latestReportReadyToSubmit = false,
        public ?\DateTime $madeDate = null,
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
