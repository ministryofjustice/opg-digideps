<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\MentalCapacity;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SignificantDecisions
{
    public ?SummaryList $list = null;
    public bool $hasSignificantDecisions;

    public bool $hasCapacityChanged;

    public bool $hasReasonForNoDecisions;

    public string $capacityChangedTranslation;

    public string $assessmentDateDisplay;

    public string $significantDecisionsMade;

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
        $mentalCapacity = $report->getMentalCapacity();
        $significantDecisions = $report->getDecisions();

        $this->hasSignificantDecisions = $report->getSignificantDecisionsMade() === 'yes';
        $this->hasCapacityChanged = $mentalCapacity?->getHasCapacityChanged() === 'changed';
        $this->hasReasonForNoDecisions = !is_null($report->getReasonForNoDecisions());

        $this->capacityChangedTranslation = match($mentalCapacity->getHasCapacityChanged()) {
            'stayedSame' => 'review.capactiyUnchanged',
            'changed' => 'review.capactiyChanged',
            default => 'review.notEntered'
        };

        $this->parameters = ['%client%' => $report->getClient()->getFirstname()];
        $this->text = $this->makeText($report->get104TransSuffix());


        $this->list = $this->makeList($report, $mentalCapacity);
        $this->table = $this->makeTable();
    }

    private function makeList(Report $report, MentalCapacity $mentalCapacity): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['capacityChangedLabel'], $this->text['capacityChanged']);

        if ($this->hasCapacityChanged) {
            $builder->addItem($this->text['capacityChangedDetailsLabel'], $mentalCapacity->getHasCapacityChangedDetails());
        }

        $assessmentDateDisplay = !is_null($mentalCapacity?->getMentalAssessmentDate()) ?
            ucfirst($mentalCapacity->getMentalAssessmentDate()->format('m/Y')) :
            $this->text['notEntered'];
        $builder->addItem($this->text['assessmentDateLabel'], $assessmentDateDisplay);

        $significantDecisionsMade = !is_null($report->getSignificantDecisionsMade()) ?
            ucfirst($report->getSignificantDecisionsMade()) :
            $this->text['notEntered'];
        $builder->addItem($this->text['significantDecisionsLabel'], $significantDecisionsMade);

        if ($this->hasReasonForNoDecisions) {
            $builder->addItem($this->text['reasonForNoDecision'], $report->getReasonForNoDecisions());
        }

        return $builder->makeList();
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
            'notEntered' => $this->translate('review.notEntered'),
            'assessmentDateLabel' => $this->translate('mentalCapacity.form.mentalAssessmentDate.legend'),
            'capacityChangedLabel' => $this->translate("mentalCapacity.form.hasCapacityChanged.label{$suffix}"),
            'capacityChanged' => $this->translate($this->capacityChangedTranslation),
            'capacityChangedDetailsLabel' => $this->translate('summary.capacityChangeDetails'),
            'significantDecisionLabel' => $this->translate('existPage.form.hasDecisions.label'),
            'reasonForNoDecision' => $this->translate('summary.reasonNoDecisions'),
            'listOfDecisionsTitle' => $this->translate('review.listOfSignificantDecisions'),
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
