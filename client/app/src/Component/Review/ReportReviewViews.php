<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ReportReviewViews
{
    public function __construct(private Report $report, private TranslatorInterface $translator)
    {
    }

    public function getClientBenefitsCheckReviewView(): ClientBenefitsCheckReviewView
    {
        $section = new ClientBenefitsCheckReviewView($this->translator);
        $section->mount($this->report);
        return $section;
    }

    public function getDebtsReviewView(): DebtsReviewView
    {
        $section = new DebtsReviewView($this->translator);
        $section->mount($this->report);
        return $section;
    }
}
