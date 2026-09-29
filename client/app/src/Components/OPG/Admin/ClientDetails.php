<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Admin;

use OPG\Digideps\Frontend\Components\GOV\Caption;
use OPG\Digideps\Frontend\Components\GOV\Div;
use OPG\Digideps\Frontend\Components\GOV\Link;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Twig\Filters;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ClientDetails
{
    /** @var array<string, string> $text */
    public array $text = [];

    private array $parameters = [];

    public ?Table $activeReportsTable = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mount(Client $client): void
    {
        $this->text = $this->makeText();

        $reportsByCategory = $this->categoriseReports($client->getReports());

        $this->activeReportsTable = $this->makeActiveReportsTable($reportsByCategory['active']);
    }

    /**
     * @return array<string, string>
     */
    private function makeText(): array
    {
        $keys = [
            'actions',
            'dueDate',
            'manage',
            'period',
            'reportsHeading',
            'reportStatus.active',
            'type',
        ];

        return array_reduce($keys, function (array $sofar, string $key): array {
            $sofar[$key] = $this->translate($key);
            return $sofar;
        }, []);
    }

    private function translate(string $key): string
    {
        try {
            return $this->translator->trans("opg.admin.clientDetails.{$key}", $this->parameters, 'twig-components');
        } catch (\Throwable $t) {
            return "$t";
        }
    }

    /**
     * @param array<Report> $reports
     * @return array{active: array<Report>}
     */
    private function categoriseReports(array $reports): array
    {
        $categorisedReports = ['active' => []];

        foreach ($reports as $report) {
            $reportSubmitted = ($report->getSubmitted() === true);
            $reportUnsubmitted = ($report->getUnSubmitDate() !== null);
            $reportHasActiveCourtOrder = $report->hasActiveCourtOrder();

            if (!$reportSubmitted && !$reportUnsubmitted && $reportHasActiveCourtOrder) {
                $categorisedReports['active'][] = $report;
            }
        }

        return $categorisedReports;
    }

    /**
     * @param array<Report> $activeReports
     */
    private function makeActiveReportsTable(array $activeReports): Table
    {
        $caption = new Caption(text: $this->text['reportStatus.active'], size: 's', tag: Filters::statusToTagCss('active'));

        $actionsCell = new Cell(new Div($this->text['actions'], isVisuallyHidden: true), isHeader: true);

        $tableBuilder = new TableBuilder(caption: $caption)
            ->addColumns(1, 1, 1, 1)
            ->addHeader($this->text['period'], $this->text['type'], $this->text['dueDate'], $actionsCell);

        foreach ($activeReports as $activeReport) {
            $manageUrl = $this->urlGenerator->generate('admin_report_manage', ['id' => $activeReport->getId()]);

            $tableBuilder->addRow(
                str_replace(' to ', '-', $activeReport->getPeriod()),
                "OPG{$activeReport->getType()}",
                $activeReport->getDueDate()->format('j F Y'),
                new Link(href: $manageUrl, text: $this->text['manage'])
            );
        }

        return $tableBuilder->makeTable();
    }
}
