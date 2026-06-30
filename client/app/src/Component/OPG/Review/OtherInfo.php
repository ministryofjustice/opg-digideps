<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\OPG\Review;

use OPG\Digideps\Frontend\Component\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Component\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class OtherInfo
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
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['anythingElse'], $this->text[$report->getActionMoreInfo() ?? 'notEntered']);
        if ($report->getActionMoreInfo() === 'yes') {
            $builder->addItem($this->text['anythingElseDetails'], $report->getActionMoreInfoDetails() ?? $this->text['notEntered']);
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
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'anythingElse' => $this->translate('form.actionMoreInfo.label'),
            'anythingElseDetails' => $this->translate('form.actionMoreInfoDetails.label')
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-more-info');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
