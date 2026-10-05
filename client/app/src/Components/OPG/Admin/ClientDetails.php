<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Admin;

use OPG\Digideps\Frontend\Components\GOV\Caption;
use OPG\Digideps\Frontend\Components\GOV\Div;
use OPG\Digideps\Frontend\Components\GOV\Link;
use OPG\Digideps\Frontend\Components\GOV\Table\Cell;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use OPG\Digideps\Frontend\Components\OPG\Renderable\ActionsList;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Twig\Filters;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ClientDetails
{
    /** @var array<string, string> $text */
    public array $text;

    public ?Table $activeReportsTable = null;
    public ?Table $submittedReportsTable = null;
    public ?Table $incompleteReportsTable = null;
    public ?Table $closedReportsTable = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
        $this->text = $this->makeText();
    }

    public function mount(Client $client): void
    {
        $userIsSuperAdmin = in_array(User::ROLE_SUPER_ADMIN, $this->security->getUser()?->getRoles() ?? []);

        $reportsByCategory = $this->categoriseReports($client->getReports());

        $this->activeReportsTable = $this->makeReportsTable($reportsByCategory['active'], 'active', $userIsSuperAdmin);
        $this->submittedReportsTable = $this->makeReportsTable($reportsByCategory['submitted'], 'submitted', $userIsSuperAdmin, needsSubmittedColumn: true);
        $this->incompleteReportsTable = $this->makeReportsTable($reportsByCategory['incomplete'], 'incomplete', $userIsSuperAdmin);
        $this->closedReportsTable = $this->makeReportsTable($reportsByCategory['closed'], 'closed', $userIsSuperAdmin, needsManageLink: false);
    }

    /**
     * @return array<string, string>
     */
    private function makeText(): array
    {
        $keys = [
            'actionsHeader',
            'actions.checklist',
            'actions.download',
            'actions.manage',
            'dueDate',
            'period',
            'report',
            'reportsHeading',
            'reportStatus.active',
            'reportStatus.closed',
            'reportStatus.incomplete',
            'reportStatus.submitted',
            'submitted',
            'type',
        ];

        /** @var array<string, string> $translations */
        $translations = array_reduce($keys, function (array $sofar, string $key): array {
            $sofar[$key] = $this->translate($key);
            return $sofar;
        }, []);

        return $translations;
    }

    private function translate(string $key): string
    {
        try {
            return $this->translator->trans("opg.admin.clientDetails.{$key}", [], 'twig-components');
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

            if (!$reportSubmitted && !$reportUnsubmitted && $reportHasActiveCourtOrder) {
                $categorisedReports['active'][] = $report;
            } elseif ($reportSubmitted) {
                $categorisedReports['submitted'][] = $report;
            } elseif ($reportUnsubmitted) {
                $categorisedReports['incomplete'][] = $report;
            } else {
                $categorisedReports['closed'][] = $report;
            }
        }

        return $categorisedReports;
    }

    /**
     * @param array<Report> $reports
     */
    private function makeReportsTable(
        array $reports,
        string $captionKey,
        bool $userIsSuperAdmin = false,
        bool $needsManageLink = true,
        bool $needsSubmittedColumn = false
    ): ?Table {
        if (empty($reports)) {
            return null;
        }

        $caption = new Caption(
            text: $this->text["reportStatus.{$captionKey}"],
            size: 's',
            tag: Filters::statusToTagCss($captionKey)
        );

        $actionsCell = new Cell(new Div([$this->text['actionsHeader']], isVisuallyHidden: true), isHeader: true);

        $columns = [1, 1, 1, 1];
        $header = [$this->text['period'], $this->text['type'], $this->text['dueDate']];

        if ($needsSubmittedColumn) {
            $columns[] = 1;
            $header[] = $this->text['submitted'];
        }

        $header[] = $actionsCell;

        $tableBuilder = new TableBuilder(caption: $caption)
            ->addColumns(...$columns)
            ->addHeader(...$header);

        foreach ($reports as $report) {
            $period = str_replace(' to ', '-', $report->getPeriod());

            $links = [];

            if ($needsManageLink) {
                $manageUrl = $this->urlGenerator->generate('admin_report_manage', ['id' => $report->getId()]);
                $accessText = "{$period} {$this->text['report']}";
                $links[] = new Link(href: $manageUrl, text: $this->text['actions.manage'], accessibilityText: $accessText);
            }

            if ($report->isCheckable()) {
                $checklistUrl = $this->urlGenerator->generate('admin_report_checklist', ['id' => $report->getId()]);
                $links[] = new Link(href: $checklistUrl, text: $this->text['actions.checklist']);
            }

            if ($report->isDownloadable() && $userIsSuperAdmin) {
                $downloadUrl = $this->urlGenerator->generate('report_pdf', ['reportId' => $report->getId()]);
                $links[] = new Link(href: $downloadUrl, text: $this->text['actions.download']);
            }

            $cells = [
                $period,
                "OPG{$report->getType()}",
                $report->getDueDate()?->format('j F Y') ?? ''
            ];

            if ($needsSubmittedColumn) {
                $cells[] = $report->getSubmitDate()?->format('j F Y') ?? '';
            }

            $cells[] = new ActionsList($links);

            $tableBuilder->addRow(...$cells);
        }

        return $tableBuilder->makeTable();
    }
}
