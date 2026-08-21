<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Decisions
{
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
        $mentalCapacity = $report->getMentalCapacity();
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['capacityChangedLabel'], $this->translate('review.' . ($report->getMentalCapacity()?->getHasCapacityChanged() ?? 'notEntered')));
        if ($mentalCapacity?->getHasCapacityChanged() === 'changed') {
            $builder->addItem($this->text['capacityChangedDetailsLabel'], $mentalCapacity->getHasCapacityChangedDetails() ?? $this->text['notEntered']);
        }

        $builder->addItem($this->text['assessmentDateLabel'], $mentalCapacity?->getMentalAssessmentDate()?->format('m/Y') ?? $this->text['notEntered']);
        $builder->addItem($this->text['significantDecisionsLabel'], $report->getSignificantDecisionsMade() ?? $this->text['notEntered']);

        if ($report->getSignificantDecisionsMade() === 'No') {
            $builder->addItem($this->text['reasonForNoDecisions'], $report->getReasonForNoDecisions() ?? $this->text['notEntered']);
        }

        return $builder->makeList();
    }

    public function makeTable(Report $report): ?Table
    {
        if ($report->getSignificantDecisionsMade() !== 'Yes') {
            return null;
        }
        $builder = new TableBuilder()->addColumns(1, 1, 1)->addHeader(
            $this->text['decisionDescription'],
            $this->text['clientInvolved'],
            $this->text['clientInvolvedDetail'],
        );
        foreach ($report->getDecisions() as $decision) {
            $builder->addRow(
                $decision->getDescription(),
                $this->text[$decision->isClientInvolvedBoolean() ? 'yes' : 'no'],
                $decision->isClientInvolvedDetails() ?? $this->text['notEntered']
            );
        }

        return $builder->makeTable();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(string $suffix): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'assessmentDateLabel' => $this->translate('mentalCapacity.form.mentalAssessmentDate.legend'),
            'capacityChangedLabel' => $this->translate("mentalCapacity.form.hasCapacityChanged.label{$suffix}"),
            'capacityChangedDetailsLabel' => $this->translate('summaryPage.capacityChangeDetails'),
            'significantDecisionsLabel' => $this->translate('existPage.form.hasDecisions.label'),
            'reasonForNoDecisions' => $this->translate('summaryPage.reasonNoDecisions'),
            'listOfDecisionsTitle' => $this->translate('review.listOfSignificantDecisions'),
            'decisionDescription' => $this->translate('summaryPage.decisionTableColumnHeading1'),
            'clientInvolved' => $this->translate('summaryPage.decisionTableColumnHeading2'),
            'clientInvolvedDetail' => $this->translate('summaryPage.decisionTableColumnHeading3'),
            'tableHeader' => $this->translate('summaryPage.listOfDecisions')
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-decisions');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
