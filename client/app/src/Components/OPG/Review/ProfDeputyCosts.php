<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\ProfDeputyInterimCost;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ProfDeputyCosts
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?SummaryList $list = null;
    public ?Table $table = null;

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
        $this->table = $this->makeTable($report);
    }

    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['howCharged'], $this->text[$report->getProfDeputyCostsHowCharged() ?? 'notEntered']);
        $builder->addItem($this->text['hasPrevious'], $this->text[$report->getProfDeputyCostsHasPrevious() ?? 'notEntered']);
        if ($report->getProfDeputyCostsHasPrevious() === 'yes') {
            foreach ($report->getProfDeputyPreviousCosts() as $previousCost) {
                $builder->addItem(
                    "{$this->text['receivedFor']} {$previousCost->getStartDate()?->format('j F Y')} - {$previousCost->getEndDate()?->format('j F Y')}",
                    $this->tryFormatMoney($previousCost->getAmount(), $this->text['notEntered'])
                );
            }
        }
        if ($report->getProfDeputyCostsHowCharged() === 'fixed' || $report->getProfDeputyCostsHasInterim() === 'no') {
            $builder->addItem($this->text['paidForThisPeriod'], $this->tryFormatMoney($report->getProfDeputyFixedCost(), $this->text['notEntered']));
        }
        if (in_array($report->getProfDeputyCostsHowCharged(), ['assessed', 'both'])) {
            $builder->addItem($this->text['underPracticeDirection19B'], $this->text[$report->getProfDeputyCostsHasInterim() ?? 'notEntered']);
            if ($report->getProfDeputyCostsHasInterim() === 'yes') {
                $interims = $report->getProfDeputyInterimCosts();
                usort($interims, fn (ProfDeputyInterimCost $left, ProfDeputyInterimCost $right) => $left->getDate()?->getTimestamp() <=> $right->getDate()?->getTimestamp());
                $i = 0;
                foreach ($interims as $interim) {
                    $i++;
                    $builder->addItem("{$this->text['costForInterim']} {$i}", $this->tryFormatMoney($interim->getAmount(), $this->text['notEntered']) . ', ' . $this->text['paid'] . ' ' . $interim->getDate()?->format('j F Y'));
                }
            }
            $builder->addItem($this->text['amountToScco'], $report->getProfDeputyCostsAmountToScco() === null ? $this->text['notEntered'] : $this->formatMoney($report->getProfDeputyCostsAmountToScco()));
        }
        if (!empty($report->getProfDeputyCostsReasonBeyondEstimate())) {
            $builder->addItem('beyondEstimate', $report->getProfDeputyCostsReasonBeyondEstimate());
        }
        return $builder->makeList();
    }

    private function makeTable(Report $report): Table
    {
        $builder = new TableBuilder()->addColumns(1, 1)->addHeader($this->text['costType'], $this->text['amount']);

        foreach ($report->getProfDeputyOtherCosts() as $cost) {
            $label = $report->getProfDeputyOtherCostTypeIds()[$cost->getProfDeputyOtherCostTypeId()]['typeId'];
            $builder->addRow($this->translate("breakdown.form.entries.{$label}.label"), new Cell($this->tryFormatMoney($cost->getAmount(), '-'), format: self::NUMERIC_FORMAT));
        }
        $builder->addRow(new Cell($this->text['totalCosts'], isHeader: true), new Cell($this->tryFormatMoney($report->getProfDeputyTotalCosts(), $this->text['incomplete']), isBold: true));
        return $builder->makeTable();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'howCharged' => $this->translate('howCharged.form.profDeputyCostsHow.label'),
            'hasPrevious' => $this->translate('previousReceivedExists.form.profDeputyCostsHasPrevious.label'),
            'receivedFor' => $this->translate('review.receivedFor'),
            'paidForThisPeriod' => $this->translate('summaryPage.questionLabel.paidThisPeriod'),
            'underPracticeDirection19B' => $this->translate('interimExists.form.profDeputyCostsHasInterim.label'),
            'costForInterim' => $this->translate('review.costForInterim'),
            'amountToScco' => $this->translate('amountToScco.form.profDeputyCostsAmountToScco.label'),
            'totalCosts' => $this->translate('summaryPage.questionLabel.totaPaidThisPeriod'),
            'tableHeader' => $this->translate('summaryPage.breakdownOfAdditionalCosts'),
            'costType' => $this->translate('summaryPage.item'),
            'amount' => $this->translate('summaryPage.amount'),
            'notEntered' => $this->translate('review.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'fixed' => $this->translate('howCharged.form.options.fixed'),
            'assessed' => $this->translate('howCharged.form.options.assessed'),
            'both' => $this->translate('howCharged.form.options.both'),
            'incomplete' => '-',
        ];
    }

    private function tryFormatMoney(null|string|float $value, string $ifNull): string
    {
        if ($value === null) {
            return $ifNull;
        }
        return $this->formatMoney((float)$value);
    }

    private function formatMoney(float $value): string
    {
        return '£' . number_format($value, 2);
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-prof-deputy-costs');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
