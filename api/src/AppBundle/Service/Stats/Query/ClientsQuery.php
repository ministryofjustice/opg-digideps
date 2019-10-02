<?php

namespace AppBundle\Service\Stats\Query;

class ClientsQuery extends Query
{
    /**
     * @return string
     */
    protected function getAggregation(): string
    {
        return 'COUNT(DISTINCT t.clientId)';
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
            c.id as clientId,
            CASE
                WHEN EXISTS(SELECT 1 FROM odr WHERE client_id = c.id) THEN 'lay'
                WHEN r.type LIKE '%-5' THEN 'prof'
                WHEN r.type LIKE '%-6' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            r.type reportType
        FROM client c
        LEFT JOIN (SELECT client_id, type FROM report r UNION SELECT client_id, 'ndr' FROM odr o) r ON r.client_id = c.id";
    }
}
