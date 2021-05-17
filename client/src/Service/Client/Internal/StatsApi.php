<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClientInterface;

class StatsApi
{
    protected const GET_ACTIVE_LAY_REPORT_DATA_ENDPOINT = 'stats/deputies/lay/active';

    private RestClientInterface $restClient;

    public function __construct(RestClientInterface $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @return array
     */
    public function getActiveLayReportData()
    {
        return $this->restClient->get(
            self::GET_ACTIVE_LAY_REPORT_DATA_ENDPOINT,
            'array',
            ['active-users']
        );
    }
}
