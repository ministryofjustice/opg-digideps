<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Client;
use Psr\Log\LoggerInterface;

final readonly class ReportCleaner
{
    public function __construct(private EntityManagerInterface $entityManager, private LoggerInterface $verboseLogger)
    {
    }

    public function clean(bool $dryRun, bool $allowNonContinuous, int ...$clientIds): string
    {
        $log = 'caseNumber,orderUid,reportId,orderMadeDate,reportStartDate,reportEndDate,action,status';
        $clientIds = empty($clientIds) ? $this->getAllClientIds() : $clientIds;
        $count = count($clientIds);

        foreach ($clientIds as $i => $clientId) {
            $this->entityManager->clear();
            $nextLogs = $this->cleanClient($clientId, $dryRun, $allowNonContinuous);
            if (!empty($nextLogs)) {
                $lines = count($nextLogs);
                $this->verboseLogger->notice("[{$i}/{$count}] ClientId {$clientId}: {$lines} lines");
                $log .= "\r\n" . implode("\r\n", $nextLogs);
            }
        }

        return $log;
    }

    /**
     * @return array<string>
     */
    private function cleanClient(int $clientId, bool $dryRun, bool $allowNonContinuous): array
    {
        $client = $this->entityManager->find(Client::class, $clientId);
        $logs = [];
        if ($client instanceof Client) {
            $inspector = new ClientInspector($client);
            if (!$inspector->isSane()) {
                foreach ($inspector->insaneReports as $insaneReport) {
                    $logs[] = new CleanupProblem($client, $insaneReport, 'Client linked to report differs from client liked to court order linked to report.')->toCsvLine();
                }
            }
            if (!$inspector->isClean()) {
                if (!$allowNonContinuous && !$inspector->isContinuous()) {
                    $logs[] = new CleanupProblem($client, null, 'Reports are not continuous.')->toCsvLine();
                } else {
                    $actions = $inspector->getCleaningActions();
                    foreach ($actions as $action) {
                        if ($action instanceof CleanupProblem) {
                            $logs[] = $action->toCsvLine();
                        } elseif ($action instanceof CleanupPlan) {
                            $ok = true;
                            $logLine = $action->toCsvLine();
                            if ($dryRun) {
                                $logs[] = "$logLine,dry-run";
                            } else {
                                try {
                                    if (!$action->keep) {
                                        $ok = $this->entityManager->getConnection()->executeStatement("
                                            DELETE FROM court_order_report
                                            WHERE court_order_id = {$action->courtOrder->getId()}
                                            AND report_id = {$action->report->getId()}
                                        ") > 0;
                                    }
                                } catch (\Throwable $throwable) {
                                    $ok = false;
                                }
                                if ($ok) {
                                    $logs[] = "$logLine,success";
                                } else {
                                    $logs[] = "$logLine,error";
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $logs[] = "Could not fetch client entity with id {$clientId}";
        }

        return $logs;
    }

    /**
     * @return array<int>
     */
    public function getAllClientIds(): array
    {
        /**
         * @var array<int> $ids
         */
        $ids = $this->entityManager->getConnection()->executeQuery('SELECT id FROM client')->fetchFirstColumn();
        return $ids;
    }
}
