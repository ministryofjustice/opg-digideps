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
    public ?Table $submittedReportsTable = null;
    public ?Table $incompleteReportsTable = null;
    public ?Table $closedReportsTable = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function mount(Client $client): void
    {
        $this->text = $this->makeText();

        $reportsByCategory = $this->categoriseReports($client->getReports());

        $this->activeReportsTable = $this->makeReportsTable($reportsByCategory['active'], 'reportStatus.active');
        $this->submittedReportsTable = $this->makeReportsTable($reportsByCategory['submitted'], 'reportStatus.submitted');
        $this->incompleteReportsTable = $this->makeReportsTable($reportsByCategory['incomplete'], 'reportStatus.incomplete');
        $this->closedReportsTable = $this->makeReportsTable($reportsByCategory['closed'], 'reportStatus.closed', needsManageLink: false);
    }

    /**
     * @return array<string, string>
     */
    private function makeText(): array
    {
        $keys = [
            'actions',
            'checklist',
            'dueDate',
            'manage',
            'period',
            'report',
            'reportsHeading',
            'reportStatus.active',
            'reportStatus.incomplete',
            'reportStatus.submitted',
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
     * @return array{active: array<Report>, submitted: array<Report>, incomplete: array<Report>, closed: array<Report>}
     */
    private function categoriseReports(array $reports): array
    {
        $categorisedReports = [
            'submitted' => [],
            'incomplete' => [],
            'active' => [],
            'closed' => [],
        ];

        foreach ($reports as $report) {
            $reportSubmitted = ($report->getSubmitted() === true);
            $reportUnsubmitted = ($report->getUnSubmitDate() !== null);
            $reportHasActiveCourtOrder = $report->hasActiveCourtOrder();

            if ($reportSubmitted) {
                $categorisedReports['submitted'][] = $report;
            } elseif ($reportUnsubmitted) {
                $categorisedReports['incomplete'][] = $report;
            } elseif ($reportHasActiveCourtOrder) {
                $categorisedReports['active'][] = $report;
            } else {
                $categorisedReports['closed'][] = $report;
            }
        }

        return $categorisedReports;
    }

    /**
     * @param array<Report> $reports
     */
    private function makeReportsTable(array $reports, string $captionKey, bool $needsManageLink = true): ?Table
    {
        if (empty($reports)) {
            return null;
        }

        $caption = new Caption(text: $this->text[$captionKey], size: 's', tag: Filters::statusToTagCss($captionKey));

        $actionsCell = new Cell(new Div([$this->text['actions']], isVisuallyHidden: true), isHeader: true);

        $tableBuilder = new TableBuilder(caption: $caption)
            ->addColumns(1, 1, 1, 1)
            ->addHeader($this->text['period'], $this->text['type'], $this->text['dueDate'], $actionsCell);

        foreach ($reports as $report) {
            $links = [];
            $period = str_replace(' to ', '-', $report->getPeriod());

            if ($needsManageLink) {
                $manageUrl = $this->urlGenerator->generate('admin_report_manage', ['id' => $report->getId()]);
                $accessText = "{$period} {$this->text['report']}";
                $links[] = new Link(href: $manageUrl, text: $this->text['manage'], accessibilityText: $accessText);
            }

            if ($report->isCheckable()) {
                $checklistUrl = $this->urlGenerator->generate('admin_report_checklist', ['id' => $report->getId()]);
                $links[] = new Link(href: $checklistUrl, text: $this->text['checklist']);
            }

            if ($report->isDownloadable()) {

            }

            $tableBuilder->addRow(
                $period,
                "OPG{$report->getType()}",
                $report->getDueDate()->format('j F Y'),
                new Div($links)
            );
        }

        return $tableBuilder->makeTable();
    }
}
