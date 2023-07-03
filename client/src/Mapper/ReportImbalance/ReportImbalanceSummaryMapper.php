<?php

declare(strict_types=1);

namespace App\Mapper\ReportImbalance;

use App\Mapper\DateRangeQuery;
use App\Service\Client\RestClient;

class ReportImbalanceSummaryMapper
{
    const API_ENDPOINT = '/stats/report/imbalance';

    public function __construct(private RestClient $restClient)
    {}

    public function getBy(DateRangeQuery $query)
    {
        return $this->restClient->get($this->generateApiUrl($query), 'Report\ImbalanceSummary[]', ['ImbalanceSummary']);
    }

    private function generateApiUrl(DateRangeQuery $query): string
    {
        $queryStringArray = [
            'orderBy' => $query->getOrderBy(),
            'order' => $query->getSortOrder(),
        ];

        if ($query->getStartDate()) {
            $queryStringArray['fromDate'] = $query->getStartDate()->format('Y-m-d');
        }

        if ($query->getEndDate()) {
            $queryStringArray['toDate'] = $query->getEndDate()->format('Y-m-d');
        }

        return sprintf('%s?%s', self::API_ENDPOINT, http_build_query($queryStringArray));
    }
}
