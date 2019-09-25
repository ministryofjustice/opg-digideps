<?php

namespace AppBundle\Service\Stats\Metrics;

class MetricReportsSubmittedQuery extends MetricQuery
{
    protected $aggregation = 'COUNT(1)';
    protected $supportedDimensions = ['deputyType', 'reportType'];

    /**
     * @return string
     */
    protected function getSubQuery()
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
