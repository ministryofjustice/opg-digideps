<?php

namespace App\Mapper\ReportSubmission;

use App\Mapper\DateRangeQuery;
use App\Service\Client\RestClient;

class ReportSubmissionSummaryMapper
{
    /** @var RestClient */
    private $restClient;

    /** @var string */
    const API_ENDPOINT = '/report-submission/pre-registration-data';

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @return mixed
     */
    public function getBy(DateRangeQuery $query)
    {
        return $this->restClient->get($this->generateApiUrl($query), 'Report\ReportSubmissionSummary[]');
    }

    /**
     * @return string
     */
    private function generateApiUrl(DateRangeQuery $query)
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
