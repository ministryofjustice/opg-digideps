<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

final class ReportAccessService
{
    /**
     * @var array<bool, array<int, array<int>>>
     */
    private array $cache = [true => [], false => []];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<int>
     */
    public function getVisibleReportIdsGivenUserId(int $userId, bool $blockDischargedDeputies = true): array
    {
        if (!array_key_exists($userId, $this->cache[$blockDischargedDeputies])) {
            $this->cache[$blockDischargedDeputies][$userId] = $this->fetchVisibleReportIdsGivenUserId($userId, $blockDischargedDeputies);
        }
        return $this->cache[$blockDischargedDeputies][$userId];
    }

    /**
     * @return array<int>
     */
    private function fetchVisibleReportIdsGivenUserId(int $userId, bool $blockDischargedDeputies): array
    {
        $active = $blockDischargedDeputies ? 'AND cod.is_active' : '';
        $sql = "
            SELECT DISTINCT r.id
            FROM report r
            JOIN court_order_report cor
                ON r.id = cor.report_id
            JOIN court_order co
                ON co.id = cor.court_order_id
            JOIN court_order_deputy cod
                ON co.id = cod.court_order_id
                {$active}
            JOIN deputy d
                ON d.id = cod.deputy_id
            JOIN client c
                ON co.client_id = c.id
            LEFT JOIN organisation o
                ON o.id = d.organisation_id
                AND o.is_activated
            LEFT JOIN organisation_user ou
                ON ou.organisation_id = o.id
            WHERE
                (d.user_id = ? OR ou.user_id = ?)
                AND c.archived_at IS NULL
                AND c.deleted_at IS NULL
        ";
        /**
         * @var array<int|string> $reportIds;
         */
        $reportIds = $this->entityManager->getConnection()->executeQuery(
            $sql,
            [$userId, $userId],
            [Types::INTEGER, Types::INTEGER]
        )->fetchFirstColumn();
        return array_map('intval', $reportIds);
    }
}
