<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\List\ListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MoneyOutShort
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
        $builder->addItem($this->text['moneyOutExists'], $report->getMoneyOutExists() ?? $this->text['notEntered']);

        if ($report->getMoneyOutExists() === 'Yes') {
            $listBuilder = new ListBuilder(true);
            foreach ($report->getMoneyShortCategoriesOutPresent() as $category) {
                $listBuilder->addItem($this->translate("form.categoriesEntries.{$category->getTypeId()}.label"));
            }
            $builder->addItem(
                $this->text['categoriesOut'],
                $listBuilder->makeUnorderedList()
            );
            $builder->addItem(
                $this->text['moneyTransactionsShortOutExist'],
                $this->text[$report->getMoneyTransactionsShortOutExist()] ?? $this->text['notEntered']
            );
        }

        if ($report->getMoneyOutExists() === 'No') {
            $builder->addItem($this->text['reasonForNoMoneyOut'], $report->getReasonForNoMoneyOut() ?? $this->text['notEntered']);
        }

        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->getMoneyTransactionsShortOutExist() === 'no') {
            return null;
        }
        $total = 0.0;

        $builder = new TableBuilder();

        $builder->addHeader(
            $this->text['description'],
            $this->text['date'],
            $this->text['amount'],
        );

        foreach ($report->getMoneyTransactionsShortOut() as $entry) {
            if ($entry->getDate() !== null) {
                $date = $entry->getDate()->format("j F Y");
            } else {
                $date = '';
            }

            $builder->addRow(
                $entry->getDescription() ?? '',
                $date,
                new Cell($this->formatMoney((float)($entry->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $total += $entry->getAmount() ?? 0.0;
        }
        $builder->addRow(new Cell($this->text['above£1kTransactionsTotal'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, isBold: true));

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
            'header' => $this->translate('summaryPage.moneyOut.pageTitle'),
            'moneyOutExists' => $this->translate('summaryPage.moneyOut.hasMoneyOut.label'),
            'reasonForNoMoneyOut' => $this->translate('summaryPage.moneyOut.reasonForNoMoneyOut.label'),
            'categoriesOut' => $this->translate('form.categoriesOut.label'),
            'moneyTransactionsShortOutExist' => $this->translate('summaryPage.moneyOut.moneyTransactionsShortOutExist.label'),
            'description' => $this->translate('summaryPage.moneyOut.list.label.description'),
            'date' => $this->translate('summaryPage.moneyOut.list.label.date'),
            'amount' => $this->translate('summaryPage.moneyOut.list.label.amount'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'tableHeader' => $this->translate('summaryPage.moneyOut.listOfExpenses'),
            'notEntered' => $this->translate('review.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'above£1kTransactionsTotal' => $this->translate('review.totalAmount'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-money-short');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
