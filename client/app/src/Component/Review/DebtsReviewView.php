<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Component\GovUk\List\ListEntries;
use OPG\Digideps\Frontend\Component\GovUk\Table\Cell;
use OPG\Digideps\Frontend\Component\GovUk\Table\Table;
use OPG\Digideps\Frontend\Component\GovUk\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DebtsReviewView
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?ListEntries $list = null;
    public ?Table $table1 = null;
    public ?Table $table2 = null;

    /**
     * @var array<string, string> $l10n
     */
    public array $l10n = [];

    private array $parameters = [];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(Report $report): void
    {
        $this->parameters = ['%client%' => $report->getClient()->getFirstname()];
        $this->l10n = $this->makeL10n();

        $this->list = $this->makeList($report);
        $this->table1 = $this->makeTable1($report);
        $this->table2 = $this->makeTable2($report);
    }

    private function makeList(Report $report): ListEntries
    {
        $builder = new ListBuilder();
        $builder->addEntry($this->l10n['hasDebts'], $this->l10n[$report->getHasDebts() ?? 'notEntered']);
        return $builder->makeList();
    }

    private function makeTable1(Report $report): ?Table
    {
        if ($report->getHasDebts() !== 'yes') {
            return null;
        }

        $builder = new TableBuilder()->addHeader($this->l10n['description'], $this->l10n['amount']);
        $total = 0.0;
        foreach (['care-fees', 'credit-cards', 'loans', 'other'] as $type) {
            $debt = $report->getDebtById($type);
            $amount = (float)$debt?->getAmount();
            if ($amount > 0.0) {
                $total += $amount;
                $builder->addRow(
                    $this->translate("form.entries.{$type}.label"),
                    new Cell($this->formatMoney($amount), self::NUMERIC_FORMAT)
                );
            }
        }
        $builder->addRow(new Cell($this->l10n['totalAmount'], isHeader: true), new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, true));
        return $builder->makeTable();
    }

    private function makeTable2(Report $report): ?Table
    {
        if ($report->getHasDebts() !== 'yes') {
            return null;
        }

        $builder = new TableBuilder()->addHeader($this->l10n['question'], $this->l10n['answer']);

        $other = $report->getDebtById('other');
        $otherAmount = (float)$other?->getAmount();
        if ($other !== null && $otherAmount > 0.0) {
            $builder->addRow("{$this->l10n['otherDebt']} {$this->formatMoney($otherAmount)}", $other->getMoreDetails() ?? $this->l10n['notEntered']);
        }
        $builder->addRow($this->l10n['howManaged'], $report->getDebtManagement() ?? $this->l10n['notEntered']);

        return $builder->makeTable();
    }

    /**
     * @return  array<string, string>
     */
    private function makeL10n(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'hasDebts' => $this->translate('existPage.form.exist.label'),
            'otherDebt' => $this->translate('review.otherDebt'),
            'description' => $this->translate('review.description'),
            'amount' => $this->translate('review.amount'),
            'totalAmount' => $this->translate('review.totalAmount'),
            'howManaged' => $this->translate('managementPage.form.debtManagement.label'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'tableHeader' => $this->translate('review.list'),
            'notEntered' => $this->translate('review.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
        ];
    }

    private function formatMoney(float $value): string
    {
        return '£ ' . number_format($value, 2);
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-debts');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
