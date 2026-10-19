<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Components\OPG\Renderable\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MoneyTransfers
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
        if (count($report->getMoneyTransfers()) > 0) {
            $this->table = $this->makeTable($report);
        }
    }

    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['noTransfersToAdd'], $report->getNoTransfersToAdd() ? $this->text['no'] : $this->text['yes'] ?? $this->text['notEntered']);

        return $builder->makeList();
    }

    private function makeTable(Report $report): Table
    {
        $builder = new TableBuilder();

        $builder
            ->addColumns(1, 1, 1, 1)
            ->addHeader(
                $this->text['accountFrom'],
                $this->text['accountTo'],
                $this->text['description'],
                $this->text['amount'],
            );

        foreach ($report->getMoneyTransfers() as $transfer) {
            $fromAccount = $transfer->getAccountFrom();
            $toAccount = $transfer->getAccountTo();
            $builder->addRow(
                new BankAccount(
                    is_string($fromAccount->getAccountTypeText()) ? $fromAccount->getAccountTypeText() : '',
                    is_string($fromAccount->getAccountNumber()) ? $fromAccount->getAccountNumber() : '',
                    $fromAccount->requiresSortCode() ? $fromAccount->getDisplaySortCode() : null,
                    is_string($fromAccount->getBank()) ? $fromAccount->getBank() : '',
                    $fromAccount->getIsJointAccount() === 'yes',
                    $fromAccount->getIsClosed() === true,
                ),
                new BankAccount(
                    is_string($toAccount->getAccountTypeText()) ? $toAccount->getAccountTypeText() : '',
                    is_string($toAccount->getAccountNumber()) ? $toAccount->getAccountNumber() : '',
                    $toAccount->requiresSortCode() ? $toAccount->getDisplaySortCode() : null,
                    is_string($toAccount->getBank()) ? $toAccount->getBank() : '',
                    $toAccount->getIsJointAccount() === 'yes',
                    $toAccount->getIsClosed() === true,
                ),
                $transfer->getDescription() ?? '',
                new Cell($this->formatMoney((float)($transfer->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
        }

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
            'noTransfersToAdd' => $this->translate('existPage.form.noTransfersToAdd.label'),
            'accountFrom' => $this->translate('summaryPage.accountFrom'),
            'accountTo' => $this->translate('summaryPage.accountTo'),
            'description' => $this->translate('summaryPage.description'),
            'amount' => $this->translate('summaryPage.amount'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'tableHeader' => $this->translate('review.tableHeader'),
            'notEntered' => $this->translate('review.notEntered'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-money-transfer');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
