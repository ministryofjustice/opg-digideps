<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryList;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\OPG\Renderable\Address;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Deputy;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ReportHeader
{
    public ?SummaryList $reportInformation = null;
    public ?SummaryList $deputyDetails = null;
    public ?SummaryList $clientDetails = null;

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
        $this->parameters = [
            '%client%' => $report->getClient()->getFirstname(),
            '%reportTypeHeading%' => $report->get104TransSuffix() === '-4' ? 'property and financial, and health and welfare' : (str_contains($report->getType(), '104') ? 'health and welfare' : 'property and financial'),
        ];
        $this->text = $this->makeText();

        $this->reportInformation = $this->makeReportInformationList($report);
        $this->deputyDetails = $this->makeDeputyDetailsList($report->getPrimaryDeputy());
        $this->clientDetails = $this->makeClientDetailsList($report->getClient());
    }

    private function makeReportInformationList(Report $report): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['caseNumber'], $report->getClient()->getCaseNumber());
        $builder->addItem($this->text['reportingStart'], $report->getStartDate()?->format('d/m/Y') ?? '');
        $builder->addItem($this->text['reportingEnd'], $report->getEndDate()?->format('d/m/Y') ?? '');
        return $builder->makeList();
    }

    private function makeDeputyDetailsList(?Deputy $deputy): ?SummaryList
    {
        if ($deputy === null) {
            return null;
        }
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['firstname'], $deputy->getFirstname());
        $builder->addItem($this->text['lastname'], $deputy->getFirstname());
        $builder->addItem($this->text['address'], new Address(
            $deputy->getAddress1(),
            $deputy->getAddress2(),
            $deputy->getAddress3(),
            $deputy->getAddress4(),
            $deputy->getAddress5(),
            $deputy->getAddressPostcode(),
            $deputy->getAddressCountry()
        ));
        $builder->addItem($this->text['phone'], $deputy->getPhoneMain() ?? $deputy->getPhoneAlternative() ?? '');
        return $builder->makeList();
    }

    private function makeClientDetailsList(Client $client): SummaryList
    {
        $builder = new SummaryListBuilder();
        $builder->addItem($this->text['firstname'], $client->getFirstname());
        $builder->addItem($this->text['lastname'], $client->getLastname());
        $builder->addItem($this->text['address'], new Address(
            $client->getAddress(),
            $client->getAddress2(),
            $client->getAddress3(),
            $client->getAddress4(),
            $client->getAddress5(),
            $client->getPostcode(),
            $client->getCountry()
        ));
        $builder->addItem($this->text['phone'], $client->getPhone() ?? '');
        return $builder->makeList();
    }

    /**
     * @return  array<string, string>
     */
    private function makeText(): array
    {
        $keys = [
            'header',
            'reportInformation',
            'deputyDetails',
            'noDeputy',
            'clientDetails',
            'firstname',
            'lastname',
            'address',
            'phone',
            'caseNumber',
            'reportingStart',
            'reportingEnd',
        ];
        return array_map(fn (string $key): string => $this->translate("opg.review.reportHeader.{$key}"), array_combine($keys, $keys));
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, $this->parameters, 'twig-components');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}
