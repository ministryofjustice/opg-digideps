<?php

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\VisitsCare as ReportVisitsCare;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class VisitsCare
{
    public ?SummaryList $list = null;

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
        $this->list = $this->makeList($report->getVisitsCare());
    }


    private function makeList(ReportVisitsCare $visitsCare): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['livesWithClient'], $this->translate('review.' . ($visitsCare->getDoYouLiveWithClient() ?? 'notEntered')));
        if ($visitsCare->getDoYouLiveWithClient() === 'no') {
            $builder->addItem($this->text['clientContact'], $visitsCare->getHowOftenDoYouContactClient() ?? 'notEntered');
        }
        $builder->addItem($this->text['clientReceivePaidCare'], $this->translate('review.' . ($visitsCare->getDoesClientReceivePaidCare() ?? 'notEntered')));
        if ($visitsCare->getDoesClientReceivePaidCare() === 'yes') {
            $builder->addItem($this->text['careFunded'], $this->translate("form.howIsCareFunded.choices.{$visitsCare->getHowIsCareFunded()}"));
        }
        $builder->addItem($this->text['careGiver'], $this->translate($visitsCare->getWhoIsDoingTheCaring() ?? $this->text['notEntered']));
        $builder->addItem($this->text['clientHasACarePlan'], $this->translate('review.' . ($visitsCare->getDoesClientHaveACarePlan() ?? 'notEntered')));
        if ($visitsCare->getDoesClientHaveACarePlan() === 'yes') {
            $date = $visitsCare->getWhenWasCarePlanLastReviewed();
            $builder->addItem($this->text['lastTimeCarePlanReviewed'], $date?->format('F Y') ?? 'notEntered');
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
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'livesWithClient' => $this->translate('form.doYouLiveWithClient.label'),
            'clientContact' => $this->translate("form.howOftenDoYouContactClient.labelShort"),
            'clientReceivePaidCare' => $this->translate("form.doesClientReceivePaidCare.label"),
            'careFunded' => $this->translate("form.howIsCareFunded.label"),
            'careGiver' => $this->translate("form.whoIsDoingTheCaring.label"),
            'clientHasACarePlan' => $this->translate("form.doesClientHaveACarePlan.label"),
            'lastTimeCarePlanReviewed' => $this->translate("form.whenWasCarePlanLastReviewed.label"),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-visits-care');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
