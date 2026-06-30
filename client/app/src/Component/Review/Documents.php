<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\Review;

use OPG\Digideps\Frontend\Component\GovUk\List\DefinitionList;
use OPG\Digideps\Frontend\Component\GovUk\List\ListBuilder;
use OPG\Digideps\Frontend\Component\GovUk\Table\Table;
use OPG\Digideps\Frontend\Component\GovUk\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]

final class Documents
{
    public ?DefinitionList $list = null;
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
        $this->text = $this->makeText();
        $this->list = $this->makeList($report);
        $this->table = $this->makeTable($report);
    }


    private function makeList(Report $report): DefinitionList
    {
        $builder = new ListBuilder();
        $builder->addItem($this->text['documentsProvided'], $this->text[$report->getWishToProvideDocumentation() ?? 'notEntered']);
        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->getWishToProvideDocumentation() !== 'yes') {
            return null;
        }

        $builder = new TableBuilder()->addHeader($this->text['filename'], $this->text['dateAttached']);
        foreach ($report->getDocuments() as $document) {
            $builder->addRow($document->getFileName() ?? '', $document->getCreatedOn()?->format('d F Y H:i') ?? '');
        }
        return $builder->makeTable();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('pageTitle'),
            'question' => $this->translate('review.question'),
            'answer' => $this->translate('review.answer'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
            'notEntered' => $this->translate('review.notEntered'),
            'documentsProvided' => $this->translate('attachPage.step1Heading'),
            'tableHeader' => $this->translate('attachPage.documentList'),
            'filename' => $this->translate('attachPage.filename'),
            'dateAttached' => $this->translate('attachPage.dateAttached'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-documents');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
