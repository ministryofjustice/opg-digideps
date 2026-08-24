<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Cleanup\Model\Client;
use OPG\Digideps\Backend\Cleanup\Model\Problem;
use OPG\Digideps\Backend\Cleanup\Model\Query;
use OPG\Digideps\Backend\Entity\Cleanup\ReportCleanupProblem;
use OPG\Digideps\Backend\Entity\Counter\Counter;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use Psr\Log\LoggerInterface;

final readonly class ReportCleaner
{
    private Counter $counter;
    private Query $query;
    private Connection $connection;

    public function __construct(private EntityManagerInterface $entityManager, private LoggerInterface $verboseLogger)
    {
        $this->counter = new Counter();
        $this->connection = $this->entityManager->getConnection();
        $this->query = new Query($this->connection, $this->verboseLogger);
    }

    public function clean(bool $allowNonContinuous, int ...$clientIds): void
    {
        $this->connection->executeStatement('DELETE FROM report_cleanup_action WHERE TRUE');
        $this->connection->executeStatement('DELETE FROM report_cleanup_problem WHERE TRUE');
        $this->cleanType($allowNonContinuous, CourtOrderType::PFA, $clientIds);
        $this->cleanType($allowNonContinuous, CourtOrderType::HW, $clientIds);
    }

    private function cleanType(bool $allowNonContinuous, CourtOrderType $type, array $clientIds): void
    {
        try {
            $count = count($clientIds);
            if ($count === 0) {
                $count = (int)$this->connection->executeQuery('SELECT COUNT(id) FROM client')->fetchFirstColumn()[0];
            }
            $startTime = microtime(true);
            $this->entityManager->clear();

            foreach ($this->query->run($type, ...$clientIds) as $i => $client) {
                memory_reset_peak_usage();

                $this->cleanClient($client, $allowNonContinuous);

                if ($this->counter->nextInt() > 256) {
                    $this->counter->reset();
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    $elapsed = microtime(true) - $startTime;
                    $expected = (int)round($elapsed * $count / ($i + 1));
                    $elapsed = (int)round($elapsed);
                    $peekMemory = (int)round(memory_get_peak_usage() / 1024 / 1024);
                    $this->verboseLogger->notice("[{$i}/{$count}][{$elapsed}s/{$expected}s][{$peekMemory}MiB] CaseNumber:n {$client->caseNumber}");
                }
            }
        } catch (\Throwable $throwable) {
            $this->verboseLogger->error(sprintf("Unexpected error '%s': %s", $throwable::class, $throwable->getMessage()));
        }
        $this->counter->reset();
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function cleanClient(Client $client, bool $allowNonContinuous): void
    {
        try {
            $inspector = new ClientInspector($client);
            if (!$inspector->isClean()) {
                if (!$allowNonContinuous && !$inspector->isContinuous()) {
                    $this->entityManager->persist(new ReportCleanupProblem($client->clientId, null, null, Problem::NotContinuous));
                } else {
                    foreach ($inspector->getCleaningActions() as $action) {
                        $this->entityManager->persist($action);
                        $this->counter->nextInt();
                    }
                }
            }
        } catch (\Throwable $throwable) {
            $this->verboseLogger->error(sprintf("Unexpected error '%s' for client with id %s: %s", $throwable::class, $client->clientId, $throwable->getMessage()));
        }
    }

    public function executeActions(): int
    {
        $rows = (int)$this->connection->executeStatement('
            DELETE FROM court_order_report cor
            USING report_cleanup_action rca
            WHERE
                rca.report_id = cor.report_id
                AND rca.order_id = cor.court_order_id
                AND NOT rca.keep
        ');
        $this->verboseLogger->notice("Removed {$rows} rows from court_order_report.");
        return $rows;
    }

    public function generateActionReport(int ...$clientIds): string
    {
        $csv = null;
        $clientClause = empty($clientIds) ? '' : sprintf("WHERE c.id IN (%s)", implode(',', $clientIds));
        foreach ($this->connection->executeQuery("
            SELECT
                c.case_number,
                c.id AS client_id,
                rca.order_id,
                o.court_order_uid AS order_uid,
                o.status = 'ACTIVE' AS order_open,
                o.order_made_date,
                rca.report_id,
                r.start_date,
                r.end_date,
                r.due_date,
                r.submit_date,
                r.un_submit_date,
                CASE WHEN rca.keep THEN 'KEEP' ELSE 'DROP' END AS action
            FROM report_cleanup_action rca
            JOIN report r ON r.id = rca.report_id
            JOIN client c ON r.client_id = c.id
            JOIN court_order o ON rca.order_id = o.id
            {$clientClause}
        ")->iterateAssociative() as $row) {
            $csv ??= implode(',', array_keys($row)) . "\r\n";
            $csv .=  implode(',', $row) . "\r\n";
        }
        return $csv ?? 'NO ACTIONS PLANNED';
    }

    public function generateProblemReport(int ...$clientIds): string
    {
        $csv = null;
        $clientClause = empty($clientIds) ? '' : sprintf("WHERE c.id IN (%s)", implode(',', $clientIds));
        foreach ($this->connection->executeQuery("
            SELECT
                c.case_number,
                c.id AS client_id,
                rcp.order_id,
                o.court_order_uid AS order_uid,
                o.status = 'ACTIVE' AS order_open,
                o.order_made_date,
                rcp.report_id,
                r.start_date,
                r.end_date,
                r.due_date,
                r.submit_date,
                r.un_submit_date,
                rcp.problem
            FROM report_cleanup_problem rcp
            LEFT JOIN report r ON r.id = rcp.report_id
            LEFT JOIN client c ON r.client_id = rcp.client_id
            LEFT JOIN court_order o ON o.id = rcp.order_id
            {$clientClause}
        ")->iterateAssociative() as $row) {
            $csv ??= implode(',', array_keys($row)) . "\r\n";
            $csv .=  implode(',', $row) . "\r\n";
        }
        return $csv ?? 'NO PROBLEMS FOUND';
    }
}
