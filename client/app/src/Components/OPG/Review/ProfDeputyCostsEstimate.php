<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ProfDeputyCostsEstimate
{
    public ?SummaryList $list = null;

    /**
     * @var array<string, string> $text
     */
    public array $text = [];

    private array $parameters = [];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(Report $report): void
    {
        $this->parameters = ['%client%' => $report->getClient()->getFirstname()];
        $this->text = $this->makeText();
        $this->list = $this->makeList($report);
    }

    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['howCharged'], $this->text[$report->getProfDeputyCostsEstimateHowCharged() ?? 'notEntered']);
        if (in_array($report->getProfDeputyCostsEstimateHowCharged(), ['assessed', 'both'])) {
            $builder->addItem(
                $this->text['generalCosts'],
                $this->formatMoney($report->getProfDeputyManagementCostAmount())
            );
            $moreInfoText = $report->getProfDeputyCostsEstimateHasMoreInfo() === 'yes' ? $report->getProfDeputyCostsEstimateMoreInfoDetails() : $this->text['noMoreInfo'];
            $builder->addItem($this->text['moreInfo'], $moreInfoText);
        }
        return $builder->makeList();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'howCharged' => $this->translate('howCharged.form.profDeputyCostsEstimateHowCharged.label'),
            'generalCosts' => $this->translate('breakdown.form.profDeputyCostsEstimateManagementCost.sectionDescription'),
            'moreInfo' => $this->translate('moreInfo.form.profDeputyCostsEstimateHasMoreInfo.label'),
            'noMoreInfo' => $this->translate('summaryPage.noMoreInfo'),
            'notEntered' => $this->translate('review.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'fixed' => $this->translate('howCharged.form.options.fixed'),
            'assessed' => $this->translate('howCharged.form.options.assessed'),
            'both' => $this->translate('howCharged.form.options.both'),
        ];
    }

    private function formatMoney(float $value): string
    {
        return '£' . number_format($value, 2);
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-prof-deputy-costs-estimate');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
