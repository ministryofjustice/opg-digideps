<?php

declare(strict_types=1);

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
final class MoneyIn
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
        if ($report->getMoneyInExists() === 'Yes') {
            $this->table = $this->makeTable($report);
        }
    }

    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['moneyInExists'], $report->getMoneyInExists() ?? $this->text['notEntered']);

        if ($report->getMoneyInExists() === 'No') {
            $builder->addItem($this->text['reasonForNoMoneyIn'], $report->getReasonForNoMoneyIn() ?? $this->text['notEntered']);
        }

        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->getMoneyInExists() ==  'no') {
            return null;
        }
        $total = 0.0;

        $builder = new TableBuilder();

        $builder->addHeader(
            $this->text['type'],
            $this->text['description'],
            $this->text['bankAccount'],
            $this->text['amount'],
        );

        foreach ($report->getMoneyTransactionsIn() as $entry) {
            $builder->addRow(
                $this->translate('form.category.entries.' . $entry->getCategory() . '.label'),
                $entry->getDescription() ?? $this->text['notEntered'],
                $entry->getBankAccount() ?? $this->text['notEntered'],
                new Cell($this->formatMoney((float)($entry->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $total += $entry->getAmount() ?? 0.0;
        }
        $builder->addRow(new Cell($this->text['totalMoneyInAmount'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, true));

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
            'header' => $this->translate('summaryPage.moneyIn.pageTitle'),
            'moneyInExists' => $this->translate('summaryPage.moneyIn.hasMoneyIn.label'),
            'reasonForNoMoneyIn' => $this->translate('summaryPage.moneyIn.reasonForNoMoneyIn.label'),
            'type' => $this->translate('summaryPage.moneyIn.list.label.type'),
            'description' => $this->translate('summaryPage.moneyIn.list.label.description'),
            'bankAccount' => $this->translate('summaryPage.moneyIn.list.label.bankAccount'),
            'amount' => $this->translate('summaryPage.moneyIn.list.label.amount'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'tableHeader' => $this->translate('review.moneyIn.income'),
            'notEntered' => $this->translate('review.moneyIn.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'totalMoneyInAmount' => $this->translate('review.moneyIn.totalMoneyIn'),
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
