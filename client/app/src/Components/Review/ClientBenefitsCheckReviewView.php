<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\Review;

use OPG\Digideps\Frontend\Components\GovUk\Table\Cell;
use OPG\Digideps\Frontend\Components\GovUk\Table\Table;
use OPG\Digideps\Frontend\Components\GovUk\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Frontend\Entity\Report\Report;

final class ClientBenefitsCheckReviewView
{
    public ?Table $table1 = null;
    public ?Table $table2 = null;
    /**
     * @var array<string, string> $l10n
     */
    public array $l10n = [];

    public function __construct(Report $report)
    {
        $clientBenefitsCheck = $report->getClientBenefitsCheck();
        $this->l10n = $this->makeL10n();
        if ($clientBenefitsCheck !== null) {
            $this->table1 = $this->makeTable1($clientBenefitsCheck);
            $this->table2 = $this->makeTable2($clientBenefitsCheck);
        }
    }

    private function makeTable1(ClientBenefitsCheck $clientBenefitsCheck): Table
    {
        $builder = new TableBuilder(true);

        $builder->addRow($this->l10n['benefitsCheck'], "form.whenLastChecked.choices.{$clientBenefitsCheck->getWhenLastCheckedEntitlement()}");
        if ($clientBenefitsCheck->getWhenLastCheckedEntitlement() === 'haveChecked') {
            $builder->addRow($this->l10n['dateChecked'], $clientBenefitsCheck->getDateLastCheckedEntitlement()?->format("m Y") ?? '');
        }
        if ($clientBenefitsCheck->getWhenLastCheckedEntitlement() === 'neverChecked') {
            $builder->addRow($this->l10n['neverChecked'], $clientBenefitsCheck->getNeverCheckedExplanation() ?? '');
        }
        $builder->addRow($this->l10n['doOthersReceiveMoney'], "form.moneyOnClientsBehalf.choices.{$clientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf()}");
        if ($clientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf() === 'dontKnow') {
            $builder->addRow($this->l10n['dontKnow'], $clientBenefitsCheck->getDontKnowMoneyExplanation() ?? '');
        }

        return $builder->makeTable();
    }

    private function makeTable2(ClientBenefitsCheck $clientBenefitsCheck): ?Table
    {
        if ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()?->isEmpty() ?? true) {
            return null;
        }

        $builder = new TableBuilder()->addHeader(
            $this->l10n['paymentType'],
            $this->l10n['paymentRecipient'],
            $this->l10n['paymentAmount'],
        );
        foreach (($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() ?? []) as $entry) {
            $builder->addRow(
                $entry->getMoneyType() ?? '',
                $entry->getWhoReceivedMoney() ?? '',
                new Cell(($entry->getAmountDontKnow() ?? false) ? $this->l10n['dontKnowAmount'] : $this->formatMoney((float)($entry->getAmount() ?? 0)), 'numeric')
            );
        }

        return $builder->makeTable();
    }

    private function formatMoney(float $value): string
    {
        return '£ ' . number_format($value, 2);
    }

    public bool $hasTable2 {get {
        return $this->table2 !== null;
    }}

    /**
     * @return  array<string, string>
     */
    private function makeL10n(): array
    {
        return [
            'header' => 'common.pageTitle',
            'question' => "summaryPage.table.benefitsCheck.column1Title",
            'answer' => "summaryPage.table.benefitsCheck.column2Title",
            'benefitsCheck' => 'stepPage.pageTitle.1.mainTitle',
            'doOthersReceiveMoney' => 'stepPage.pageTitle.2.mainTitle',
            'dateChecked' => "form.whenLastChecked.dateCheckedHint",
            'neverChecked' => "form.whenLastChecked.neverCheckedHint",
            'dontKnow' => "form.moneyOnClientsBehalf.choices.dontKnow",
            'dontKnowAmount' => "form.moneyDetails.dontKnowCheckboxLabel",
            'table2Header' => "summaryPage.table.moneyOtherPeopleReceive.title",
            'paymentType' => "summaryPage.table.moneyOtherPeopleReceive.column1Title",
            'paymentRecipient' => "summaryPage.table.moneyOtherPeopleReceive.column2Title",
            'paymentAmount' => "summaryPage.table.moneyOtherPeopleReceive.column3Title",
        ];
    }
}
