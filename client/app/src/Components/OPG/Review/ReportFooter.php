<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\OPG\RichText\RichTextParser;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ReportFooter
{
    public ?SummaryList $list = null;
    public ?RenderableInterface $declarationInfo = null;

    /**
     * @var array<string, string> $text
     */
    public array $text = [];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(Report $report): void
    {
        $this->text = $this->makeText();

        $this->list = $this->makeList($report);
        $this->declarationInfo = $this->makeRichText();
    }

    private function makeRichText(): RenderableInterface
    {
        return new RichTextParser($this->translator)->parse('opg.review.reportFooter.info', 'twig-components');
    }

    private function makeList(Report $report): ?SummaryList
    {
        $declaration = $report->getAgreedBehalfDeputy();
        if ($declaration === null) {
            return null;
        }

        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['declaration'], $this->text[$declaration]);
        if ($declaration === 'more_deputies_not_behalf') {
            $builder->addItem($this->text['whyNot'], $report->getAgreedBehalfDeputyExplanation() ?? $this->text['notEntered']);
        }
        $builder->addItem($this->text['declarationTime'], $report->getSubmitDate()?->format('h:i d/m/Y') ?? '');
        $builder->addItem($this->text['submittedBy'], $report->getSubmittedBy()?->getFullName() ?? '');
        return $builder->makeList();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        $keys = [
            'header',
            'cardHeader',
            'declaration',
            'declarationTime',
            'submittedBy',
            'not_deputy',
            'only_deputy',
            'more_deputies_behalf',
            'more_deputies_not_behalf',
            'whyNot',
            'notEntered'
        ];
        return array_map(fn (string $key): string => $this->translate("opg.review.reportFooter.{$key}"), array_combine($keys, $keys));
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, [], 'twig-components');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}
