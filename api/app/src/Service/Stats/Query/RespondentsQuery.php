<?php

namespace App\Service\Stats\Query;

class RespondentsQuery extends Query
{
    protected function getAggregation(): string
    {
        return 'COUNT(1)';
    }

    protected function getSupportedDimensions(): array
    {
        return [];
    }

    protected function getSubquery(): string
    {
        return 'SELECT
            s.created_at date
        FROM satisfaction s';
    }
}
