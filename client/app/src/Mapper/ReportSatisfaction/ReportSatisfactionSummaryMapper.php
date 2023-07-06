<?php

declare(strict_types=1);

namespace App\Mapper\ReportSatisfaction;

use App\Mapper\DateRangeQuery;
use App\Service\Client\RestClient;

class ReportSatisfactionSummaryMapper
{
    /** @var RestClient */
    private $restClient;

    /** @var string */
    const API_ENDPOINT = '/satisfaction/satisfaction_data';

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @return mixed
     */
    public function getBy(DateRangeQuery $query)
    {
        return $this->restClient->get($this->generateApiUrl($query), 'Report\Satisfaction[]', ['satisfaction']);
    }

    /**
     * @return string
     */
    private function generateApiUrl(DateRangeQuery $query)
    {
        $queryArray = [
            'orderBy' => $query->getOrderBy(),
            'order' => $query->getSortOrder(),
        ];

        if ($query->getStartDate()) {
            $queryArray['fromDate'] = $query->getStartDate()->format('Y-m-d');
        }

        if ($query->getEndDate()) {
            $queryArray['toDate'] = $query->getEndDate()->format('Y-m-d');
        }

        return sprintf(
            '%s?%s',
            self::API_ENDPOINT,
            http_build_query($queryArray)
        );
    }
}
