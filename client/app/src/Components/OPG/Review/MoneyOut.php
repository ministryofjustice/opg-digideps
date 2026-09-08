<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MoneyOut
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?SummaryList $list = null;
    /**
     * @var array<Table>
     */
    public array $tables = [];
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
        if ($report->getMoneyOutExists() === 'Yes') {
            foreach ($report->groupMoneyTransactionsByGroup($report->getMoneyTransactionsOut()) as $transaction) {
                $this->tables[] = $this->makeTable($transaction);
            }
            $this->tables[] = $this->makeTotalTable($report);
        }
    }

    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['moneyOutExists'], $report->getMoneyOutExists() ?? $this->text['notEntered']);

        if ($report->getMoneyOutExists() === 'No') {
            $builder->addItem($this->text['reasonForNoMoneyOut'], $report->getReasonForNoMoneyOut() ?? $this->text['notEntered']);
        }

        return $builder->makeList();
    }

    private function makeTable(mixed $transaction): Table
    {
        $entries = is_array($transaction) && isset($transaction['entries']) && is_array($transaction['entries']) ? $transaction['entries'] : [];
        $firstEntry = reset($entries);
        /** @var string $group */
        $group = $firstEntry instanceof MoneyTransaction
            ? $firstEntry->getGroup()
            : null;

        $builder = new TableBuilder(caption: $this->translate('form.group.entries.' . $group))
            ->addColumns(1, 1, 1, 1)
            ->addHeader(
                $this->text['type'],
                $this->text['description'],
                $this->text['bankAccount'],
                $this->text['amount'],
            );

        $subtotal = 0.0;
        foreach ($entries as $entry) {
            $entry = $entry instanceof MoneyTransaction ? $entry : null;
            if ($entry === null) {
                continue;
            }
            /** @var BankAccount $bankAccount */
            $bankAccount = $entry->getBankAccount();
            if ($bankAccount !== null) {
                $bankAccountText = is_string($bankAccount->getNameOneline()) ? $bankAccount->getNameOneline() : $this->text['notEntered'];
            } else {
                $bankAccountText = $this->text['notEntered'];
            }
            /** @var string $categoryText */
            $categoryText = $entry->getCategory();
            $builder->addRow(
                $this->translate('form.category.entries.' . $categoryText . '.label'),
                is_string($entry->getDescription()) ? $entry->getDescription() : $this->text['notEntered'],
                $bankAccountText,
                new Cell($this->formatMoney((float)($entry->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $subtotal += (float)($entry->getAmount() ?? 0.0);
        }
        $builder->addRow(new Cell($this->text['totalAmount'], isHeader: true), '', '', new Cell($this->formatMoney($subtotal), self::NUMERIC_FORMAT, isBold: true));

        return $builder->makeTable();
    }

    private function makeTotalTable(Report $report): Table
    {
        return new TableBuilder(true)
            ->addColumns(1, 1, 1, 1)
            ->addRow(new Cell($this->text['totalMoneyOutAmount'], colspan: 3), new Cell($this->formatMoney($report->getMoneyOutTotal()), self::NUMERIC_FORMAT, isBold: true))
            ->makeTable();
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
            'type' => $this->translate('summaryPage.moneyOut.list.label.type'),
            'description' => $this->translate('summaryPage.moneyOut.list.label.description'),
            'bankAccount' => $this->translate('summaryPage.moneyOut.list.label.bankAccount'),
            'amount' => $this->translate('summaryPage.moneyOut.list.label.amount'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'tableHeader' => $this->translate('review.moneyOut.payments'),
            'notEntered' => $this->translate('review.moneyOut.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'totalAmount' => $this->translate('review.moneyOut.totalAmount'),
            'totalMoneyOutAmount' => $this->translate('review.moneyOut.totalMoneyOut'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-money-transaction');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
