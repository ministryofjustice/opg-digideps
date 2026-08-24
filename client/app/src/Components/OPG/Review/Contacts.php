<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Components\OPG\Renderable\Contact;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Contacts
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
        $this->text = $this->makeText();

        $this->list = $this->makeList($report);
        $this->table = $this->makeTable($report);
    }

    private function makeList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['hasContacts'], $this->text[$report->hasContacts() ?? 'notEntered']);
        if ($report->hasContacts() === 'no') {
            $builder->addItem($this->text['noContactsReason'], $report->getReasonForNoContacts() ?? $this->text['notEntered']);
        }

        return $builder->makeList();
    }

    private function makeTable(Report $report): ?Table
    {
        if ($report->hasContacts() !== 'yes') {
            return null;
        }
        $builder = new TableBuilder()
            ->addColumns(1, 1, 1)
            ->addHeader($this->text['contact'], $this->text['relationship'], $this->text['reasonForContact']);
        foreach ($report->getContacts() as $contact) {
            $builder->addRow(
                new Contact(
                    $contact->getContactName(),
                    $contact->getAddress(),
                    $contact->getAddress2(),
                    $contact->getCounty(),
                    $contact->getPostcode(),
                    $contact->getCountry()
                ),
                $contact->getRelationship() ?? $this->text['notEntered'],
                $contact->getExplanation() ?? $this->text['notEntered']
            );
        }
        return $builder->makeTable();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        return [
            'header' => $this->translate('startPage.pageTitle'),
            'hasContacts' => $this->translate('existPage.form.hasContacts.label'),
            'noContactReason' => $this->translate('existPage.form.reasonForNoContacts.label'),
            'notEntered' => $this->translate('review.notEntered'),
            'tableHeader' => $this->translate('summaryPage.listOfContacts'),
            'contact' => $this->translate('summaryPage.contact'),
            'relationship' => $this->translate('summaryPage.relationship'),
            'reasonForContact' => $this->translate('summaryPage.reasonForContact'),
            'yes' => $this->translate('review.yes'),
            'no' => $this->translate('review.no'),
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'report-contacts');
        } catch (\Throwable $t) {
            return "{$t}";
        }
    }
}
