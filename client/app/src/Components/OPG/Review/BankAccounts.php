<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Components\OPG\Renderable\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class BankAccounts
{
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
        $this->parameters = [
            '%client%' => $report->getClient()->getFirstname(),
            '%startDate%' => $report->getStartDate()?->format('j F Y'),
            '%endDate%' => $report->getEndDate()?->format('j F Y'),
        ];
        $this->text = $this->makeText();
        $this->table = $this->makeTable($report);
    }

    public function makeTable(Report $report): Table
    {
        $builder = new TableBuilder()
            ->addColumns(1, 1, 1)
            ->addHeader($this->text['account'], $this->text['balanceOnStart'], $this->text['balanceOnEnd']);

        foreach ($report->getBankAccounts() as $bankAccount) {
            $builder->addRow(
                new BankAccount(
                    $bankAccount->getAccountTypeText() ?? '',
                    $bankAccount->getAccountNumber() ?? '',
                    $bankAccount->requiresSortCode() ? $bankAccount->getDisplaySortCode() : null,
                    $bankAccount->requiresBankName() ? $bankAccount->getBank() : null,
                    $bankAccount->getIsJointAccount() === 'yes',
                    $bankAccount->getIsClosed()
                ),
                $this->formatBalance($bankAccount->getOpeningBalance()),
                $this->formatBalance($bankAccount->getClosingBalance())
            );
        }

        return $builder->makeTable();
    }

    private function formatBalance(null|string|int|float $value): string
    {
        if (!is_numeric($value)) {
            return $this->text['notEntered'];
        }
        return '£' . number_format((float)$value, 2);
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'tableHeader' => $this->translate('review.tableHeader'),
            'account' => $this->translate('review.account'),
            'balanceOnStart' => $this->translate('review.balanceOnStart'),
            'balanceOnEnd' => $this->translate('review.balanceOnEnd'),
            'notEntered' => $this->translate('review.notEntered'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-bank-accounts');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
