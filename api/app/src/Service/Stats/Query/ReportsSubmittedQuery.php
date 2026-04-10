<?php

namespace OPG\Digideps\Backend\Service\Stats\Query;

class ReportsSubmittedQuery extends Query
{
    protected function getAggregation(): string
    {
        return 'COUNT(1)';
    }

    protected function getSupportedDimensions(): array
    {
        return ['deputyType', 'reportType'];
    }

    protected function getSubquery(): string
    {
        return "SELECT
            rs.created_on date,
            CASE
                WHEN u.role_name LIKE '%_PROF_%' THEN 'prof'
                WHEN u.role_name LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            r.type AS reportType
        FROM report_submission rs
        INNER JOIN dd_user u ON u.id = rs.created_by
        LEFT JOIN report r ON r.id = rs.report_id";
    }
}
