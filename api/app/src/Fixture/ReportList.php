<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

final readonly class ReportList
{
    /**
     * @var array<ReportDescriptor> $reportDescriptors
     */
    public array $reportDescriptors;

    /**
     * @param bool $currentIsSubmittable If true, the latest report for the court order is filled in so that
     * it is in a submittable state. However, the made date of the court order must be one year before today,
     * otherwise that will block the submission, regardless of the state of the form.
     */
    public function __construct(public bool $currentIsSubmittable = false, ReportDescriptor ...$reportDescriptors)
    {
        $this->reportDescriptors = $reportDescriptors;
    }

    public static function noReports(): ReportList
    {
        return new ReportList();
    }

    public static function oneUnsubmittedReport(?\DateTimeImmutable $orderMadeDate = null, bool $currentIsSubmittable = false): ReportList
    {
        return new ReportList($currentIsSubmittable, new ReportDescriptor($orderMadeDate ?? new \DateTimeImmutable()->sub(new \DateInterval('P6M'))));
    }

    public static function manyReports(?\DateTimeImmutable $orderMadeDate = null, int $submittedReports = 1, bool $currentIsSubmittable = false): ReportList
    {
        if ($submittedReports < 1) {
            return ReportList::oneUnsubmittedReport($orderMadeDate, $currentIsSubmittable);
        }
        $startDate = $orderMadeDate ?? new \DateTimeImmutable()->sub(new \DateInterval('P6M'))->sub(new \DateInterval("P{$submittedReports}Y"));
        for ($i = 0; $i < $submittedReports; ++$i) {
            $descriptor = new ReportDescriptor($startDate, submitDate: $startDate->add(new \DateInterval('P12M'))->add(new \DateInterval('P15D')));
            $descriptors[] = $descriptor;
            $startDate = $descriptor->endDate->add(new \DateInterval('P1D'));
        }
        $descriptors[] = new ReportDescriptor($startDate);
        return new ReportList($currentIsSubmittable, ...$descriptors);
    }
}
