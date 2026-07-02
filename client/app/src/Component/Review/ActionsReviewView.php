<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Component\GovUk\List\DefinitionList;
use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Entity\Report\Action;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ActionsReviewView
{
    public ?DefinitionList $list = null;

    /**
     * @var array<string, string> $text
     */
    public array $text = [];

    private array $parameters = [];

    public function __construct(private readonly TranslatorInterface $translator) {}

    public function mount(Report $report): void
    {
        $this->parameters = ['%client%' => $report->getClient()->getFirstname()];
        $this->text = $this->makeText($report->get104TransSuffix());
        $this->list = $this->makeList($report->getAction());
    }


    private function makeList(Action $action): DefinitionList
    {
        $builder = new ListBuilder();
        $builder->addEntry($this->text['concerns'], $this->text[$action->getDoYouHaveConcerns() ?? 'notEntered']);
        if ($action->getDoYouHaveConcerns() === 'yes') {
            $builder->addEntry($this->text['concernsDetails'], $action->getDoYouHaveConcernsDetails() ?? $this->text['notEntered']);
        }
        $builder->addEntry($this->text['financialDecisions'], $this->text[$action->getDoYouExpectFinancialDecisions() ?? 'notEntered']);
        if ($action->getDoYouExpectFinancialDecisions() === 'yes') {
            $builder->addEntry($this->text['financialDecisionsDetails'], $action->getDoYouExpectFinancialDecisionsDetails() ?? $this->text['notEntered']);
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
