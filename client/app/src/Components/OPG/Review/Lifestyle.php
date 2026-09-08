<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Lifestyle
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
        $this->text = $this->makeText();
        $this->list = $this->makeList($report);
    }


    private function makeList(Report $report): SummaryList
    {
        $lifestyle = $report->getLifestyle();
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['careAppointments'], $lifestyle->getCareAppointments() ?? $this->text['notEntered']);
        $builder->addItem($this->text['doesClientUndertakeSocialActivities'],  $this->text[$lifestyle->getDoesClientUndertakeSocialActivities() ?? 'notEntered']);
        if ($lifestyle->getDoesClientUndertakeSocialActivities() === 'yes') {
            $builder->addItem($this->text['whichActivities'], $lifestyle->getActivityDetailsYes() ?? $this->text['notEntered']);
        }
        if ($lifestyle->getDoesClientUndertakeSocialActivities() === 'no') {
            $builder->addItem($this->text['whyNoActivities'], $lifestyle->getActivityDetailsNo() ?? $this->text['notEntered']);
        }
        return $builder->makeList();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'careAppointments' => $this->translate('form.careAppointments.label'),
            'doesClientUndertakeSocialActivities' => $this->translate('form.doesClientUndertakeSocialActivities.label'),
            'whichActivities' => $this->translate('form.activityDetailsYes.label'),
            'whyNoActivities' => $this->translate('form.activityDetailsNo.label'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-lifestyle');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
