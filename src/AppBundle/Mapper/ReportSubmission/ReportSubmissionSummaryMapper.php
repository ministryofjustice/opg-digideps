<?php

namespace AppBundle\Mapper\ReportSubmission;

use AppBundle\Service\Client\RestClient;

class ReportSubmissionSummaryMapper
{
    /** @var RestClient */
    private $restClient;

    /** @var string */
    const API_ENDPOINT = '/report-submission/casrec_data';

    /**
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param ReportSubmissionSummaryQuery $query
     * @return mixed
     */
    public function getBy(ReportSubmissionSummaryQuery $query)
    {
        return $this->restClient->get($this->generateApiUrl($query), 'Report\ReportSubmissionSummary[]');
    }

    /**
     * @param ReportSubmissionSummaryQuery $query
     * @return string
     */
    private function generateApiUrl(ReportSubmissionSummaryQuery $query)
    {
        return sprintf ('%s?%s', self::API_ENDPOINT, http_build_query([
                'fromDate' => $query->getFromDate(),
                'toDate' => $query->getToDate(),
                'orderBy' => $query->getOrderBy(),
                'order' => $query->getSortOrder()
            ])
        );
    }
}
