<?php

namespace App\Service\Stats\Query;

class SatisfactionQuery extends Query
{
    protected function getAggregation(): string
    {
        // Convert scores from 1-5 to 0-100 (Government standard)
        return 'ROUND(AVG(val - 1)*25)';
    }

    protected function getSupportedDimensions(): array
    {
        return ['deputyType', 'reportType'];
    }

    protected function getSubquery(): string
    {
        return "SELECT
            s.created_at date,
            CASE
                WHEN s.deputy_role LIKE '%_PROF_%' THEN 'prof'
                WHEN s.deputy_role LIKE '%_PA_%' THEN 'pa'
                WHEN s.deputy_role LIKE 'ROLE_LAY_DEPUTY' THEN 'lay'
                ELSE 'none'
            END deputyType,
            COALESCE(report_type, 'none') reportType,
            s.score val
        FROM satisfaction s
        WHERE (s.report_id IS NOT NULL OR s.ndr_id IS NOT NULL)";
    }
}
