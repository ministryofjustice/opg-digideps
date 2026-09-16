<?php

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PaDeputyExpenses
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?SummaryList $list = null;
    public ?Table $feesAndExpensesTable = null;
    public ?Table $otherExpensesTable = null;

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
        $this->feesAndExpensesTable = $this->makeFeesAndExpensesTable($report);
        $this->otherExpensesTable = $this->makeOtherExpensesTable($report);
    }


    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['paDeputyExpensesExist'], $this->translate('review.' . ($report->getHasFees() ?? 'notEntered')));
        if ($report->getHasFees() === 'no') {
            $builder->addItem($this->text['noPADeputyExpensesExplanation'], $report->getReasonForNoFees() ?? $this->text['notEntered']);
        }
        $builder->addItem($this->text['otherExpenses'], $this->translate('review.' . ($report->getPaidForAnything() ?? 'notEntered')));

        return $builder->makeList();
    }

    private function makeFeesAndExpensesTable(Report $report): ?Table
    {
        if ($report->getHasFees() === 'no' || $report->getHasFees() === null) {
            return null;
        }

        $builder = new TableBuilder()
            ->addColumns(1, 1, 1)
            ->addHeader(
                $this->text['category'],
                $this->text['details'],
                $this->text['amount'],
            );

        foreach ($report->getFeesWithValidAmount() as $fee) {
            /** @var string $feeText */
            $feeText = $fee->getFeeTypeId();
            $detailsText = $fee->getMoreDetails() ?? '';
            $builder->addRow(
                $this->translate('form.entries.' . $feeText . '.label'),
                $this->translate($detailsText),
                $this->formatMoney((float)$fee->getAmount())
            );
        }

        $builder->addRow(new Cell($this->text['totalAmount'], isHeader: true), '', new Cell($this->formatMoney((float)$report->getFeesTotal()), self::NUMERIC_FORMAT, isBold: true));
        return $builder->makeTable();
    }

    private function makeOtherExpensesTable(Report $report): ?Table
    {
        if ($report->getPaidForAnything() === 'no' || $report->getPaidForAnything() === null) {
            return null;
        }

        $builder = new TableBuilder()
            ->addColumns(1, 1)
            ->addHeader(
                $this->text['description'],
                $this->text['amount'],
            );

        foreach ($report->getExpenses() as $expense) {
            /** @var string $expenseText */
            $expenseText = $expense->getExplanation();
            $builder->addRow(
                $expenseText,
                $this->formatMoney((float)$expense->getAmount())
            );
        }

        $builder->addRow(new Cell($this->text['totalAmount'], isHeader: true), new Cell($this->formatMoney((float)$report->getExpensesTotal()), self::NUMERIC_FORMAT, isBold: true));
        return $builder->makeTable();
    }

    private function formatMoney(float $value): string
    {
        return '£' . number_format($value, 2);
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'feesAndExpensesHeader' => $this->translate('review.listOfDeputyFeesAndExpensesHeader'),
            'otherExpensesHeader' => $this->translate('review.listOfOtherExpensesHeader'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'paDeputyExpensesExist' => $this->translate('feeExistPage.form.hasFees.label'),
            'noPADeputyExpensesExplanation' => $this->translate('review.reasonForNoFees'),
            'otherExpenses' => $this->translate('otherExistPage.form.paidForAnything.label'),
            'category' => $this->translate('review.categories'),
            'details' => $this->translate('review.details'),
            'amount' => $this->translate('review.amount'),
            'totalAmount' => $this->translate('review.totalAmount'),
            'description' => $this->translate('review.description'),
        ];
    }


    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-pa-fee-expense');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
