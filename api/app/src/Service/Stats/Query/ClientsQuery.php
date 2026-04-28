<?php

namespace OPG\Digideps\Backend\Service\Stats\Query;

class ClientsQuery extends Query
{
    protected function getAggregation(): string
    {
        return 'COUNT(DISTINCT t.clientId)';
    }

    protected function getSupportedDimensions(): array
    {
        return ['deputyType', 'reportType'];
    }

    protected function getSubquery(): string
    {
        return "SELECT
            c.id as clientId,
            CASE
                WHEN r.type LIKE '%-5' THEN 'prof'
                WHEN r.type LIKE '%-6' THEN 'pa'
                ELSE 'lay'
            END deputyType,
            r.type reportType
        FROM client c
        LEFT JOIN report r ON r.client_id = c.id";
    }
}
