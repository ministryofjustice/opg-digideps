<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Balance
{
    public ?SummaryList $list1 = null;
    public ?SummaryList $list2 = null;

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

        $this->list1 = $this->makeBalanceList($report);
        $this->list2 = $this->makeDifferenceList($report);
    }

    private function makeBalanceList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();

        $started = $report->getStatus()->getBankAccountsState()['state'] !== 'not-started';
        if ($started) {
            $date = $report->getStartDate()?->format('j F Y');
        }
        $builder->addItem($this->text['accountsOpeningBalance'] . ($started ? " ($date)" : ''), $started ? $this->formatMoney($report->getAccountsOpeningBalanceTotal()) : $this->text['notEntered']);

        $started = $report->getStatus()->getExpensesState()['state'] !== 'not-started';
        if ($report->hasSection('deputyExpenses')) {
            $builder->addItem($this->text['deputyExpenses'], $started ? $this->formatMoney(min(-(float)$report->getExpensesTotal(), 0)) : $this->text['notEntered']);
        }

        $started = $report->getStatus()->getPaFeesExpensesState()['state'] !== 'not-started';
        $feesAndExpensesTotal = (float)$report->getFeesTotal() + (float)$report->getExpensesTotal();
        if ($report->hasSection('paDeputyExpenses')) {
            $builder->addItem($this->text['paDeputyExpenses'], $started ? $this->formatMoney(min(-$feesAndExpensesTotal, 0)) : $this->text['notEntered']);
        }

        $started = $report->getStatus()->getGiftsState()['state'] !== 'not-started';
        $builder->addItem($this->text['gifts'], $started ? $this->formatMoney(min(-(float)$report->getGiftsTotalValue(), 0)) : $this->text['notEntered']);

        $started = $report->getStatus()->getProfDeputyCostsState()['state'] !== 'not-started';
        if ($report->hasSection('profDeputyCosts')) {
            $builder->addItem($this->text['profDeputyCosts'], $started ? $this->formatMoney(min(-(float)$report->getProfDeputyTotalCostsTakenFromClient(), 0)) : $this->text['notEntered']);
        }

        $started = $report->getStatus()->getMoneyInState()['state'] !== 'not-started';
        $builder->addItem($this->text['moneyIn'], $started ? $this->formatMoney($report->getMoneyInTotal()) : $this->text['notEntered']);

        $started = $report->getStatus()->getMoneyOutState()['state'] !== 'not-started';
        $builder->addItem($this->text['moneyOut'], $started ? $this->formatMoney(min(-$report->getMoneyOutTotal(), 0)) : $this->text['notEntered']);

        $showCalculations = $report->getStatus()->getBankAccountsState()['state'] !== 'not-started';
        $builder->addItem($this->text['footer'], $showCalculations ? $this->formatMoney($report->getCalculatedBalance()) : $this->text['notEntered']);

        return $builder->makeList();
    }

    private function makeDifferenceList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();

        $started = $report->getStatus()->getBankAccountsState()['state'] !== 'not-started';
        if ($started) {
            $date = $report->getEndDate()?->format('j F Y');
        }
        $builder->addItem($this->text['accountsClosingBalance'] . ($started ? " ($date)" : ''), $started ? $this->formatMoney($report->getAccountsClosingBalanceTotal()) : $this->text['notEntered']);

        $builder->addItem($this->text['reportClosingBalance'], $started ? $this->formatMoney($report->getCalculatedBalance()) : $this->text['notEntered']);

        $builder->addItem($this->text['differenceFooter'], $this->formatMoney(abs($report->getTotalsOffset())));

        if (!$report->isTotalsMatch() && $report->getBalanceMismatchExplanation() !== null) {
            $builder->addItem($this->text['reasonNotBalancing'], ucfirst($report->getBalanceMismatchExplanation()));
        }

        return $builder->makeList();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('pageTitle'),
            'balanceHeader' => $this->translate('review.balanceCardTitle'),
            'accountsOpeningBalance' => $this->translate('balanceTable.accountsOpeningBalance'),
            'deputyExpenses' => $this->translate('balanceTable.deputyExpenses'),
            'paDeputyExpenses' => $this->translate('balanceTable.paDeputyExpenses'),
            'gifts' => $this->translate('balanceTable.gifts'),
            'profDeputyCosts' => $this->translate('balanceTable.profDeputyCosts'),
            'moneyIn' => $this->translate('balanceTable.moneyIn'),
            'moneyOut' => $this->translate('balanceTable.moneyOut'),
            'footer' => $this->translate('balanceTable.footer'),
            'differenceHeader' => $this->translate('review.differenceCardTitle'),
            'accountsClosingBalance' => $this->translate('differenceTable.accountsClosingBalance'),
            'reportClosingBalance' => $this->translate('differenceTable.reportClosingBalance'),
            'differenceFooter' => $this->translate('differenceTable.footer'),
            'reasonNotBalancing' => $this->translate('review.reasonNotBalancing'),
            'notEntered' => $this->translate('review.notEntered'),
        ];
    }

    private function formatMoney(float $value): string
    {
        return '£' . number_format($value, 2);
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-balance');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
