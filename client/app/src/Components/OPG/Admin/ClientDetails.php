<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Admin;

use OPG\Digideps\Frontend\Components\GOV\Caption;
use OPG\Digideps\Frontend\Components\GOV\Div;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Twig\Filters;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

// this will eventually contain the whole client page, but currently just renders the report tables at the bottom
#[AsTwigComponent]
final class ClientDetails
{
    /** @var array<string, string> $text */
    public array $text = [];

    private array $parameters = [];

    public ?Table $activeReportsTable = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function mount(Client $client): void
    {
        $this->parameters = [];
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
            'period',
            'reportsHeading',
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
        $actionsCell = new Cell(new Div($this->text['actions'], isVisuallyHidden: true), isHeader: true);

        $caption = new Caption(text: 'active', size: 's', tag: Filters::statusToTagCss('active'));

        $tableBuilder = new TableBuilder(caption: $caption)
            ->addColumns(1, 1, 1, 1)
            ->addHeader($this->text['period'], $this->text['type'], $this->text['dueDate'], $actionsCell);

        foreach ($activeReports as $activeReport) {
            $tableBuilder->addRow(
                str_replace(' to ', '-', $activeReport->getPeriod()),
                "OPG{$activeReport->getType()}",
                $activeReport->getDueDate()->format('j F Y'),
                'link'
            );
        }

        return $tableBuilder->makeTable();
    }
}
