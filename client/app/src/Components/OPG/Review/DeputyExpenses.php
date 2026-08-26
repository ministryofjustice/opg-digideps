<?php

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class DeputyExpenses
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
        $this->text = $this->makeText($report->get104TransSuffix());
        $this->list = $this->makeList($report);
        $this->table = $this->makeTable($report);
    }


    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['deputyExpensesExist'], $this->translate('review.' . ($report->getPaidForAnything() ?? 'notEntered')));

        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->getPaidForAnything() !== 'yes' && count($report->getExpenses()) === 0) {
            return null;
        }
        $total = 0.0;

        $builder = new TableBuilder();

        $builder->addHeader(
            $this->text['deputyExpenseExplanation'],
            $this->text['bankAccount'],
            $this->text['amount'],
        );

        foreach (($report->getExpenses()) as $deputyExpense) {
            $explanation = $deputyExpense->getExplanation();
            $explanationText = is_string($explanation) ? $explanation : '';
            /** @var BankAccount $bankAccount */
            $bankAccount = $deputyExpense->getBankAccount();
            if ($bankAccount !== null) {
                $bankAccountText = is_string($bankAccount->getNameOneline()) ? $bankAccount->getNameOneline() : $this->text['notEntered'];
            } else {
                $bankAccountText = $this->text['notEntered'];
            }

            $builder->addRow(
                $explanationText,
                $bankAccountText,
                new Cell($this->formatMoney((float)($deputyExpense->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $total += (float)($deputyExpense->getAmount() ?? 0.0);
        }
        $builder->addRow(new Cell($this->text['totalAmount'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, isBold: true));

        return $builder->makeTable();
    }

    private function formatMoney(float $value): string
    {
        return '£' . number_format($value, 2);
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(string $suffix): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'deputyExpensesExist' => $this->translate('existPage.form.paidForAnything.label'),
            'tableHeader' => $this->translate('review.tableHeader'),
            'deputyExpenseExplanation' => $this->translate('form.explanation.label'),
            'bankAccount' => $this->translate('review.bankAccount'),
            'amount' => $this->translate('form.amount.label'),
            'totalAmount' => $this->translate('review.totalAmount'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-deputy-expenses');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
