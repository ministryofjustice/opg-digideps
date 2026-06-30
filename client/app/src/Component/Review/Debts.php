<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Component\GovUk\List\DefinitionList;
use OPG\Digideps\Frontend\Component\GovUk\Table\Cell;
use OPG\Digideps\Frontend\Component\GovUk\Table\Table;
use OPG\Digideps\Frontend\Component\GovUk\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Debts
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?DefinitionList $list1 = null;
    public ?Table $table = null;
    public ?DefinitionList $list2 = null;

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

        $this->list1 = $this->makeList1($report);
        $this->table = $this->makeTable($report);
        $this->list2 = $this->makeList2($report);
    }

    private function makeList1(Report $report): DefinitionList
    {
        $builder = new ListBuilder();
        $builder->addItem($this->text['hasDebts'], $this->text[$report->getHasDebts() ?? 'notEntered']);
        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->getHasDebts() !== 'yes') {
            return null;
        }

        $builder = new TableBuilder()->addColumns(1, 1)->addHeader($this->text['description'], $this->text['amount']);
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
        $builder->addRow(new Cell($this->text['totalAmount'], isHeader: true), new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, isBold: true));
        return $builder->makeTable();
    }

    private function makeList2(Report $report): ?DefinitionList
    {
        if ($report->getHasDebts() !== 'yes') {
            return null;
        }

        $builder = new ListBuilder();

        $other = $report->getDebtById('other');
        $otherAmount = (float)$other?->getAmount();
        if ($other !== null && $otherAmount > 0.0) {
            $builder->addItem("{$this->text['otherDebt']} {$this->formatMoney($otherAmount)}", $other->getMoreDetails() ?? $this->text['notEntered']);
        }
        $builder->addItem($this->text['howManaged'], $report->getDebtManagement() ?? $this->text['notEntered']);

        return $builder->makeList();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
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
