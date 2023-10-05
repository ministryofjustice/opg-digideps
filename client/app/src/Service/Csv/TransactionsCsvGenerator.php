<?php

declare(strict_types=1);

namespace App\Service\Csv;

use App\Entity\Report\Expense;
use App\Entity\Report\Gift;
use App\Entity\Report\MoneyTransaction;
use App\Entity\ReportInterface;
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

    /**
     * @return string
     */
    public function generateTransactionsCsv(ReportInterface $report)
    {
        $headers = ['Type', 'Category', 'Amount', 'Bank name', 'Account details', 'Description'];
        $this->generateTransactionsCsvLines($report);

        return $this->csvBuilder->buildCsv($headers, $this->rows);
    }

    /**
     * Generates the lines of the CSV.
     */
    private function generateTransactionsCsvLines(ReportInterface $report)
    {
        $this->generateTransactionRows($report->getGifts(), 'gift');
        $this->generateTransactionRows($report->getExpenses(), 'expense');
        $this->generateTransactionRows($report->getMoneyTransactionsOut(), 'money out');
        $this->generateTransactionRows($report->getMoneyTransactionsIn(), 'money in');
    }

    /**
     * Generates Transaction row.
     *
     * @param $transactions
     * @param $type
     */
    private function generateTransactionRows($transactions, $type): void
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
     *
     * @param Gift|Expense|MoneyTransaction $transaction
     *
     * @return string
     */
    private function generateDescription($transaction)
    {
        if (method_exists($transaction, 'getDescription')) {
            return $transaction->getDescription();
        }

        if (method_exists($transaction, 'getExplanation')) {
            return $transaction->getExplanation();
        }

        return '';
    }

    /**
     * @param Gift|Expense|MoneyTransaction $transaction
     *
     * @return string
     */
    private function generateCategory($transaction)
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

    /**
     * @param Gift|Expense|MoneyTransaction $transaction
     *
     * @return string
     */
    private function generateBankName($transaction)
    {
        return !empty($transaction->getBankAccount()) ? $transaction->getBankAccount()->getBank() : '';
    }

    /**
     * @param Gift|Expense|MoneyTransaction $transaction
     *
     * @return string
     */
    private function generateBankAccountDetails($transaction)
    {
        return !empty($transaction->getBankAccount()) ? $transaction->getBankAccount()->getDisplayName() : '';
    }
}
