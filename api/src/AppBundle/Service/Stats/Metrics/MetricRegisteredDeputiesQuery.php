<?php

namespace AppBundle\Service\Stats\Metrics;

class MetricRegisteredDeputiesQuery extends MetricQuery
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
        return ['deputyType'];
    }

    /**
     * @return string
     */
    protected function getSubquery(): string
    {
        return "SELECT
            u.registration_date date,
            CASE
                WHEN u.role_name LIKE '%_PROF_%' THEN 'prof'
                WHEN u.role_name LIKE '%_PA_%' THEN 'pa'
                ELSE 'lay'
            END deputyType
        FROM dd_user u";
    }
}
