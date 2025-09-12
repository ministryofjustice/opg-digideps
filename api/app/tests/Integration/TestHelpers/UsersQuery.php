<?php

declare(strict_types=1);

namespace App\Tests\Integration\TestHelpers;

use App\Service\Stats\Query\Query;

class UsersQuery extends Query
{
    protected function getAggregation(): string
    {
        return 'COUNT(1)';
    }

    protected function getSupportedDimensions(): array
    {
        return ['roleName', 'ndrEnabled'];
    }

    public function getSubquery(): string
    {
        return '
            SELECT
                id,
                registration_date date,
                role_name roleName,
                odr_enabled ndrEnabled
            FROM dd_user
        ';
    }
}
