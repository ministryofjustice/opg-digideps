<?php

namespace AppBundle\Service\Stats\Query;

class SatisfactionQuery extends Query
{
    /**
     * @return string
     */
    protected function getAggregation(): string
    {
        // Convert scores from 1-5 to 0-100 (Government standard)
        return 'ROUND(AVG(val - 1)*25)';
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
            s.created_at date,
            CASE
                WHEN s.deputy_role LIKE '%_PROF_%' THEN 'prof'
                WHEN s.deputy_role LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            report_type reportType,
            s.score val
        FROM satisfaction s";
    }

}
