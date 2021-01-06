<?php

namespace AppBundle\Service\Stats\Query;

class ReportsSubmittedQuery extends Query
{
    /**
     * @return string
     */
    protected function getAggregation(): string
    {
        return 'COUNT(1)';
    }

    /**
     * @return array
     */
    protected function getSupportedDimensions(): array
    {
        return ['deputyType', 'reportType'];
    }

    /**
     * @return string
     */
    protected function getSubquery(): string
    {
        return "SELECT
            rs.created_on date,
            CASE
                WHEN u.role_name LIKE '%_PROF_%' THEN 'prof'
                WHEN u.role_name LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            CASE
                WHEN rs.ndr_id IS NOT NULL THEN 'ndr'
                ELSE r.type
            END reportType
        FROM report_submission rs
        INNER JOIN dd_user u ON u.id = rs.created_by
        LEFT JOIN report r ON r.id = rs.report_id";
    }
}
