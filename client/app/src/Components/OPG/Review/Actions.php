<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Entity\Report\Action;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Actions
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
        $this->list = $this->makeList($report->getAction());
    }


    private function makeList(Action $action): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['concerns'], $this->text[$action->getDoYouHaveConcerns() ?? 'notEntered']);
        if ($action->getDoYouHaveConcerns() === 'yes') {
            $builder->addItem($this->text['concernsDetails'], $action->getDoYouHaveConcernsDetails() ?? $this->text['notEntered']);
        }
        $builder->addItem($this->text['financialDecisions'], $this->text[$action->getDoYouExpectFinancialDecisions() ?? 'notEntered']);
        if ($action->getDoYouExpectFinancialDecisions() === 'yes') {
            $builder->addItem($this->text['financialDecisionsDetails'], $action->getDoYouExpectFinancialDecisionsDetails() ?? $this->text['notEntered']);
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
            'concerns' => $this->translate('form.doYouHaveConcerns.label'),
            'concernsDetails' => $this->translate('form.doYouHaveConcernsDetails.label'),
            'financialDecisions' => $this->translate("form.doYouExpectFinancialDecisions.label{$suffix}"),
            'financialDecisionsDetails' => $this->translate("form.doYouExpectFinancialDecisionsDetails.label{$suffix}")
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-actions');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
