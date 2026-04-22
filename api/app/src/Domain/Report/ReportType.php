<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Domain\CourtOrder\CourtOrderType;
use App\Domain\CourtOrder\CourtOrderReportType;
use App\Domain\Deputy\DeputyType;

final readonly class ReportType implements \Stringable
{
    public function __construct(
        public CourtOrderReportType $courtOrderReportType,
        public CourtOrderType $courtOrderType,
        public CourtOrderKind $courtOrderKind,
        public DeputyType $deputyType,
    ) {
    }

    public function __toString(): string
    {
        $parts = [];

        $suffix = ($this->courtOrderType !== CourtOrderType::HW || $this->courtOrderKind === CourtOrderKind::Hybrid) ?
            $this->courtOrderReportType->getSuffix() :
            CourtOrderReportType::OPG104->getSuffix();

        $parts[] = '10' . $suffix;

        if ($this->courtOrderKind === CourtOrderKind::Hybrid) {
            $parts[] = CourtOrderReportType::OPG104->getSuffix();
        }

        if ($this->deputyType !== DeputyType::LAY) {
            $parts[] = $this->deputyType->getSuffix();
        }

        return implode('-', $parts);
    }
}
