<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Common\Utility\Ghost;

/**
 * @implements \IteratorAggregate<int, ReportSection>
 */
final readonly class Sections implements \IteratorAggregate
{
    /**
     * @var array<ReportSection>
     */
    private array $sections;

    private function __construct(ReportType $type)
    {
        $this->sections = $this->calculateSections($type);
    }

    /**
     * @return array<ReportSection>
     */
    private function calculateSections(ReportType $type): array
    {
        $sections = [
            ReportSection::DECISIONS,
            ReportSection::CONTACTS,
            ReportSection::VISITS_CARE,
        ];

        if ($type->courtOrderType === CourtOrderType::PFA) {
            $sections = [
                ...$sections,
                ReportSection::CLIENT_BENEFITS_CHECK,
                ReportSection::BANK_ACCOUNTS,
                ReportSection::GIFTS
            ];
        } elseif ($type->courtOrderType === CourtOrderType::HW) {
            $sections[] = ReportSection::LIFESTYLE;
        }

        if ($type->courtOrderReportType === CourtOrderReportType::OPG102) {
            $sections = [
                ...$sections,
                ReportSection::MONEY_TRANSFERS,
                ReportSection::MONEY_IN,
                ReportSection::MONEY_OUT,
                ReportSection::BALANCE,
            ];
        } elseif ($type->courtOrderReportType === CourtOrderReportType::OPG103) {
            $sections = [
                ...$sections,
                ReportSection::MONEY_IN_SHORT,
                ReportSection::MONEY_OUT_SHORT,
            ];
        }

        if ($type->courtOrderType === CourtOrderType::PFA) {
            $sections = [
                ...$sections,
                ReportSection::ASSETS,
                ReportSection::DEBTS,
            ];
        }

        if ($type->deputyType === DeputyType::LAY) {
            $sections[] = ReportSection::DEPUTY_EXPENSES;
        } elseif ($type->deputyType === DeputyType::PRO) {
            $sections = [
                ...$sections,
                ReportSection::PROF_DEPUTY_COSTS,
                ReportSection::PROF_DEPUTY_COSTS_ESTIMATE,
            ];
        } elseif ($type->deputyType === DeputyType::PA) {
            $sections[] = ReportSection::PA_DEPUTY_EXPENSES;
        }

        return [
            ...$sections,
            ReportSection::ACTIONS,
            ReportSection::OTHER_INFO,
            ReportSection::DOCUMENTS,
        ];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->sections);
    }

    /**
     * @return array<string>
     */
    public function asSectionIdArray(): array
    {
        return array_map(fn (ReportSection $section) => $section->value, $this->sections);
    }

    public function hasSection(ReportSection $section): bool
    {
        return in_array($section, $this->sections);
    }

    public function getSectionAfter(ReportSection $section): ?ReportSection
    {
        return $this->sections[$this->find($section, 1)] ?? null;
    }

    public function getSectionBefore(ReportSection $section): ?ReportSection
    {
        return $this->sections[$this->find($section, -1)] ?? null;
    }

    private function find(ReportSection $section, int $offset = 0): int
    {
        $index = array_find_key($this->sections, fn (ReportSection $r) => $section === $r);
        if (is_int($index)) {
            return $index + $offset;
        }
        return -1;
    }

    public static function new(ReportType $reportType): Sections
    {
        return Ghost::new(Sections::class, static function (Sections $sections) use ($reportType) {
            $sections->__construct($reportType);
        });
    }
}
