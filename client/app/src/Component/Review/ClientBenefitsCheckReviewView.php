<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Component\GovUk\List\ListEntries;
use OPG\Digideps\Frontend\Component\GovUk\Table\Cell;
use OPG\Digideps\Frontend\Component\GovUk\Table\Table;
use OPG\Digideps\Frontend\Component\GovUk\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ClientBenefitsCheckReviewView
{
    private const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    public ?ListEntries $list = null;
    public ?Table $table = null;
    /**
     * @var array<string, string> $l10n
     */
    public array $l10n = [];

    private array $parameters = [];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(Report $report): void
    {
        $clientBenefitsCheck = $report->getClientBenefitsCheck();

        if ($clientBenefitsCheck !== null) {
            $this->parameters = ['%client%' => $report->getClient()->getFullname()];
            $this->l10n = $this->makeL10n();

            $this->list = $this->makeList($clientBenefitsCheck);
            $this->table = $this->makeTable($clientBenefitsCheck);
        }
    }

    private function makeList(ClientBenefitsCheck $clientBenefitsCheck): ListEntries
    {
        $builder = new ListBuilder();

        $builder->addEntry($this->l10n['benefitsCheck'], $this->translate("form.whenLastChecked.choices.{$clientBenefitsCheck->getWhenLastCheckedEntitlement()}"));
        if ($clientBenefitsCheck->getWhenLastCheckedEntitlement() === 'haveChecked') {
            $builder->addEntry($this->l10n['dateChecked'], $clientBenefitsCheck->getDateLastCheckedEntitlement()?->format("m Y") ?? '');
        }
        if ($clientBenefitsCheck->getWhenLastCheckedEntitlement() === 'neverChecked') {
            $builder->addEntry($this->l10n['neverChecked'], $clientBenefitsCheck->getNeverCheckedExplanation() ?? '');
        }
        $builder->addEntry($this->l10n['doOthersReceiveMoney'], $this->translate("form.moneyOnClientsBehalf.choices.{$clientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf()}"));
        if ($clientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf() === 'dontKnow') {
            $builder->addEntry($this->l10n['dontKnow'], $clientBenefitsCheck->getDontKnowMoneyExplanation() ?? '');
        }

        return $builder->makeList();
    }

    private function makeTable(ClientBenefitsCheck $clientBenefitsCheck): ?Table
    {
        if ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()?->isEmpty() ?? true) {
            return null;
        }
        $total = 0.0;

        $builder = new TableBuilder()->addHeader(
            $this->l10n['paymentType'],
            $this->l10n['paymentRecipient'],
            $this->l10n['paymentAmount'],
        );
        foreach (($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() ?? []) as $entry) {
            $builder->addRow(
                $entry->getMoneyType() ?? '',
                $entry->getWhoReceivedMoney() ?? '',
                new Cell(($entry->getAmountDontKnow() ?? false) ? $this->l10n['dontKnowAmount'] : $this->formatMoney((float)($entry->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $total += $entry->getAmount() ?? 0.0;
        }
        $builder->addRow(new Cell($this->l10n['paymentAmount'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT));

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
    private function makeL10n(): array
    {
        return [
            'header' => $this->translate('common.pageTitle'),
            'question' => $this->translate("summaryPage.table.benefitsCheck.column1Title"),
            'answer' => $this->translate("summaryPage.table.benefitsCheck.column2Title"),
            'benefitsCheck' => $this->translate('stepPage.pageTitle.1.mainTitle'),
            'doOthersReceiveMoney' => $this->translate('stepPage.pageTitle.2.mainTitle'),
            'dateChecked' => $this->translate("form.whenLastChecked.dateCheckedHint"),
            'neverChecked' => $this->translate("form.whenLastChecked.neverCheckedHint"),
            'dontKnow' => $this->translate("form.moneyOnClientsBehalf.choices.dontKnow"),
            'dontKnowAmount' => $this->translate("form.moneyDetails.dontKnowCheckboxLabel"),
            'tableHeader' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.title"),
            'paymentType' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.column1Title"),
            'paymentRecipient' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.column2Title"),
            'paymentAmount' => $this->translate("review.totalAmount"),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-client-benefits-check');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}
