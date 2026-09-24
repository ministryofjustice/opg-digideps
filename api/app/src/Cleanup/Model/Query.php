<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup\Model;

use Doctrine\DBAL\Connection;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class Query
{
    public function __construct(private Connection $connection, private LoggerInterface $logger, private int $limit = 256)
    {
    }

    /**
     * @return \Generator<int, Client, void, void>
     */
    public function run(CourtOrderType $type, int ...$clientIds): \Generator
    {
        try {
            foreach ($this->execute($type, $clientIds) as $client) {
                yield $client;
            }
        } catch (\Throwable $throwable) {
            $this->logger->error(sprintf("Unexpected error '%s': %s", $throwable::class, $throwable->getMessage()));
        }
    }

    /**
     * @return \Generator<int, Client, void, void>
     */
    private function execute(CourtOrderType $type, array $clientIds): \Generator
    {
        $lastId = 0;
        $rows = true;
        while (!empty($rows)) {
            $rows = $this->fetch($lastId, $type, $this->limit, $clientIds);
            $buffer = [];
            foreach ($rows as $row) {
                $clientId = (int)$row['client_id'];
                if (empty($buffer)) {
                    $lastId = $clientId;
                }
                if ($clientId !== $lastId) {
                    $client = $this->hydrate($lastId, $buffer);
                    if ($client !== null) {
                        yield $client;
                    }
                    $buffer = [];
                    $lastId = $clientId;
                }
                $buffer[] = $row;
            }
            if (!empty($buffer)) {
                $client = $this->hydrate($lastId, $buffer);
                if ($client !== null) {
                    yield $client;
                }
            }
        }
    }

    private function hydrate(int $clientId, array $rows): ?Client
    {
        try {
            return new Client(
                $clientId,
                $rows[0]['case_number'],
                $rows
            );
        } catch (\Throwable $throwable) {
            $this->logger->error(sprintf("Hydration error '%s' for client with id %s: %s", $throwable::class, $clientId, $throwable->getMessage()));
        }
        return null;
    }

    /**
     * @param array<int> $only
     * @return array<array<string, mixed>>
     */
    private function fetch(int $from, CourtOrderType $type, int $limit, array $only): array
    {
        try {
            $onlyClause = empty($only) ? '' : sprintf("AND c.id IN (%s)", implode(',', $only));
            $rows = $this->connection->executeQuery("
                SELECT
                    c.id as client_id,
                    r.id as report_id,
                    o.id as order_id,
                    o.order_made_date,
                    r.start_date as report_start_date,
                    r.end_date as report_end_date,
                    c.case_number,
                    o.court_order_uid as order_uid,
                    o.status = 'ACTIVE' as open,
                    NOT (r.submit_date IS NULL AND r.un_submit_date IS NULL) as submitted
                FROM client c
                JOIN court_order o
                    ON o.client_id = c.id
                    AND o.order_type = '{$type->value}'
                JOIN court_order_report cor
                    ON cor.court_order_id = o.id
                JOIN report r
                    ON r.id = cor.report_id
                    AND r.client_id = c.id
                WHERE c.id IN (
                    SELECT id FROM client cc
                    WHERE
                        cc.id > {$from}
                        {$onlyClause}
                    LIMIT {$limit}
                )
                ORDER BY c.id, r.id
            ")->fetchAllAssociative();
        } catch (\Throwable $throwable) {
            $rows = [];
            $this->logger->error(sprintf("Database error '%s': %s", $throwable::class, $throwable->getMessage()));
        }
        return $rows;
    }
}
