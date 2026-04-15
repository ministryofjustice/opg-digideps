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

        if ($this->courtOrderType !== CourtOrderType::HW || $this->courtOrderKind === CourtOrderKind::Hybrid) {
            $parts[] = $this->courtOrderReportType->getSuffix();
        } else {
            $parts[] = CourtOrderReportType::OPG104->getSuffix();
        }

        if ($this->courtOrderKind === CourtOrderKind::Hybrid) {
            $parts[] = substr(CourtOrderReportType::OPG104->getSuffix(), -1);
        }

        if ($this->deputyType !== DeputyType::LAY) {
            $parts[] = $this->deputyType->getSuffix();
        }

        return implode('-', $parts);
    }

    public static function tryFrom(string $value): ReportType
    {
        $courtOrderReportType = CourtOrderReportType::tryFrom('OPG' . substr($value, 0, 3)) ??
            CourtOrderReportType::OPG102;

        $courtOrderType = match ($courtOrderReportType) {
            CourtOrderReportType::OPG102, CourtOrderReportType::OPG103 => CourtOrderType::PFA,
            CourtOrderReportType::OPG104 => CourtOrderType::HW,
        };

        // Default to single as we can not determine Dual or Single at this stage
        $courtOrderKind = str_contains($value, '-4') ? CourtOrderKind::Hybrid : CourtOrderKind::Single;

        $deputyType = match (true) {
            str_contains($value, '-5') => DeputyType::PRO,
            str_contains($value, '-6') => DeputyType::PA,
            default => DeputyType::LAY,
        };

        return new ReportType($courtOrderReportType, $courtOrderType, $courtOrderKind, $deputyType);
    }
}
