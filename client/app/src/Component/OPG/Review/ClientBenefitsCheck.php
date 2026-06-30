<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\OPG\Review;

use OPG\Digideps\Frontend\Component\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Component\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Component\GOV\Table\Cell;
use OPG\Digideps\Frontend\Component\GOV\Table\Table;
use OPG\Digideps\Frontend\Component\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck as ReportClientBenefitsCheck;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ClientBenefitsCheck
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
        $clientBenefitsCheck = $report->getClientBenefitsCheck();

        if ($clientBenefitsCheck !== null) {
            $this->parameters = ['%client%' => $report->getClient()->getFirstname()];
            $this->text = $this->makeText();

            $this->list = $this->makeList($clientBenefitsCheck);
            $this->table = $this->makeTable($clientBenefitsCheck);
        }
    }

    private function makeList(ReportClientBenefitsCheck $clientBenefitsCheck): SummaryList
    {
        $builder = new SummaryListBuilder();

        $builder->addItem($this->text['benefitsCheck'], $this->translate("form.whenLastChecked.choices.{$clientBenefitsCheck->getWhenLastCheckedEntitlement()}"));
        if ($clientBenefitsCheck->getWhenLastCheckedEntitlement() === 'haveChecked') {
            $builder->addItem($this->text['dateChecked'], $clientBenefitsCheck->getDateLastCheckedEntitlement()?->format("m Y") ?? '');
        }
        if ($clientBenefitsCheck->getWhenLastCheckedEntitlement() === 'neverChecked') {
            $builder->addItem($this->text['neverCheckedExplanation'], $clientBenefitsCheck->getNeverCheckedExplanation() ?? '');
        }
        $builder->addItem($this->text['doOthersReceiveMoney'], $this->translate("form.moneyOnClientsBehalf.choices.{$clientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf()}"));
        if ($clientBenefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf() === 'dontKnow') {
            $builder->addItem($this->text['dontKnowExplanation'], $clientBenefitsCheck->getDontKnowMoneyExplanation() ?? '');
        }

        return $builder->makeList();
    }

    private function makeTable(ReportClientBenefitsCheck $clientBenefitsCheck): ?Table
    {
        if ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()?->isEmpty() ?? true) {
            return null;
        }
        $total = 0.0;

        $builder = new TableBuilder()->addColumns(1, 1, 1)->addHeader(
            $this->text['paymentType'],
            $this->text['paymentRecipient'],
            $this->text['paymentAmount'],
        );
        foreach (($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() ?? []) as $entry) {
            $builder->addRow(
                $entry->getMoneyType() ?? '',
                $entry->getWhoReceivedMoney() ?? '',
                new Cell(($entry->getAmountDontKnow() ?? false) ? $this->text['dontKnowAmount'] : $this->formatMoney((float)($entry->getAmount() ?? 0)), self::NUMERIC_FORMAT)
            );
            $total += $entry->getAmount() ?? 0.0;
        }
        $builder->addRow(new Cell($this->text['paymentTotal'], isHeader: true), '', new Cell($this->formatMoney($total), self::NUMERIC_FORMAT, isBold: true));

        return $builder->makeTable();
    }

    private function formatMoney(float $value): string
    {
        return '£ ' . number_format($value, 2);
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('common.pageTitle'),
            'question' => $this->translate("summaryPage.table.benefitsCheck.column1Title"),
            'answer' => $this->translate("summaryPage.table.benefitsCheck.column2Title"),
            'benefitsCheck' => $this->translate('stepPage.pageTitle.1.mainTitle'),
            'doOthersReceiveMoney' => $this->translate('stepPage.pageTitle.2.mainTitle'),
            'dateChecked' => $this->translate("form.whenLastChecked.dateCheckedHint"),
            'neverCheckedExplanation' => $this->translate("form.whenLastChecked.neverCheckedHint"),
            'dontKnow' => $this->translate("form.moneyOnClientsBehalf.choices.dontKnow"),
            'dontKnowExplanation' => $this->translate('form.moneyOnClientsBehalf.dontKnowLabel'),
            'dontKnowAmount' => $this->translate("form.moneyDetails.dontKnowCheckboxLabel"),
            'tableHeader' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.title"),
            'paymentType' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.column1Title"),
            'paymentRecipient' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.column2Title"),
            'paymentAmount' => $this->translate("summaryPage.table.moneyOtherPeopleReceive.column3Title"),
            'paymentTotal' => $this->translate("review.totalAmount"),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-client-benefits-check');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
