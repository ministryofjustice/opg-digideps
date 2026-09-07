<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Csv;

use OPG\Digideps\Frontend\Entity\Report\Expense;
use OPG\Digideps\Frontend\Entity\Report\Gift;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionsCsvGenerator
{
    private TranslatorInterface $translator;
    private CsvBuilder $csvBuilder;
    private array $rows = [];

    public function __construct(TranslatorInterface $translator, CsvBuilder $csvBuilder)
    {
        $this->translator = $translator;
        $this->csvBuilder = $csvBuilder;
    }

    public function generateTransactionsCsv(Report $report): string
    {
        $headers = ['Type', 'Category', 'Amount', 'Bank name', 'Account details', 'Description'];
        $this->generateTransactionsCsvLines($report);

        return $this->csvBuilder->buildCsv($headers, $this->rows);
    }

    /**
     * Generates the lines of the CSV.
     */
    private function generateTransactionsCsvLines(Report $report)
    {
        $this->generateTransactionRows($report->getGifts(), 'gift');
        $this->generateTransactionRows($report->getExpenses(), 'expense');
        $this->generateTransactionRows($report->getMoneyTransactionsOut(), 'money out');
        $this->generateTransactionRows($report->getMoneyTransactionsIn(), 'money in');
    }

    /**
     * Generates Transaction row.
     *
     * @param array<Gift|Expense|MoneyTransaction> $transactions
     */
    private function generateTransactionRows(array $transactions, string $type): void
    {
        foreach ($transactions as $transaction) {
            $this->rows[] = [
                ucfirst($type),
                $this->generateCategory($transaction),
                $transaction->getAmount(),
                $this->generateBankName($transaction),
                $this->generateBankAccountDetails($transaction),
                $this->generateDescription($transaction),
            ];
        }
    }

    /**
     * Generates a description. Expenses and Gifts have an 'explanation' property,
     * Money transactions have a description property.
     */
    private function generateDescription(Gift|Expense|MoneyTransaction $transaction): string
    {
        if (method_exists($transaction, 'getDescription')) {
            return $transaction->getDescription() ?? '';
        }

        if (method_exists($transaction, 'getExplanation')) {
            return $transaction->getExplanation() ?? '';
        }

        return '';
    }

    private function generateCategory(Gift|Expense|MoneyTransaction $transaction): string
    {
        if (property_exists($transaction, 'category')) {
            return $this->translator
                ->trans(
                    sprintf('form.category.entries.%s.label', $transaction->getCategory()),
                    [],
                    'report-money-transaction'
                );
        }

        return '';
    }

    private function generateBankName(Gift|Expense|MoneyTransaction $transaction): string
    {
        if ($transaction->getBankAccount() === null) {
            return '';
        }

        return $transaction->getBankAccount()->getBank() ?? '';
    }

    private function generateBankAccountDetails(Gift|Expense|MoneyTransaction $transaction): string
    {
        if ($transaction->getBankAccount() === null) {
            return '';
        }

        return $transaction->getBankAccount()->getDisplayName() ?? '';
    }
}
