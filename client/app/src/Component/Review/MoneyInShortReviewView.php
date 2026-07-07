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

final class MoneyInShortReviewView
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?DefinitionList $list = null;
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
        if ($report->getMoneyInExists() === 'Yes') {
            $this->table = $this->makeTable($report);
        }
    }

    private function makeList(Report $report): DefinitionList
    {
        $builder = new ListBuilder();
        $builder->addEntry($this->text['moneyInExists'], $report->getMoneyInExists() ?? $this->text['notEntered']);

        if ($report->getMoneyInExists() === 'Yes') {
            $moneyShortPresentCategories = [];
            foreach ($report->getmoneyShortCategoriesInPresent() as $category) {
                $moneyShortPresentCategories[] = $this->translate(
                    'form.categoriesEntries.' . $category->getTypeId() . '.label'
                );
            }
            $builder->addEntry(
                $this->text['categoriesIn'],
                implode(", ", $moneyShortPresentCategories)
            );
            $builder->addEntry(
                $this->text['moneyTransactionsShortInExist'],
                $this->text[$report->getMoneyTransactionsShortInExist()] ?? $this->text['notEntered']
            );
        }

        if ($report->getMoneyInExists() === 'No') {
            $builder->addEntry($this->text['reasonForNoMoneyIn'], $report->getReasonForNoMoneyIn() ?? $this->text['notEntered']);
        }

        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->getMoneyTransactionsShortInExist() ==  'no') {
            return null;
        }
        $total = 0.0;

        $builder = new TableBuilder();

        $builder->addHeader(
            $this->text['description'],
            $this->text['date'],
            $this->text['amount'],
        );

        foreach (($report->getMoneyTransactionsShortIn() ?? []) as $entry) {
            $builder->addRow(
                $entry->getDescription() ?? '',
                $entry->getDate()->format("j F Y") ?? '',
                new Cell($this->formatMoney((float)($entry->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $total += $entry->getAmount() ?? 0.0;
        }
        $builder->addRow(new Cell($this->text['above£1kTransactionsTotal'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, true));

        return $builder->makeTable();
    }

    private function formatMoney(float $value): string
    {
        return '£ ' . number_format($value, 2);
    }

    public function hasTable(): bool
    {
        return $this->table !== null;
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('summaryPage.moneyIn.pageTitle'),
            'moneyInExists' => $this->translate('summaryPage.moneyIn.hasMoneyIn.label'),
            'reasonForNoMoneyIn' => $this->translate('summaryPage.moneyIn.reasonForNoMoneyIn.label'),
            'categoriesIn' => $this->translate('form.categoriesIn.label'),
            'moneyTransactionsShortInExist' => $this->translate('summaryPage.moneyIn.moneyTransactionsShortInExist.label'),
            'description' => $this->translate('summaryPage.moneyIn.list.label.description'),
            'date' => $this->translate('summaryPage.moneyIn.list.label.date'),
            'amount' => $this->translate('summaryPage.moneyIn.list.label.amount'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'tableHeader' => $this->translate('review.list'),
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
