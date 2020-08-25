<?php declare(strict_types=1);

namespace AppBundle\Mapper\ReportSatisfaction;

use AppBundle\Service\Client\RestClient;

class ReportSatisfactionSummaryMapper
{
    /** @var RestClient */
    private $restClient;

    /** @var string */
    const API_ENDPOINT = '/satisfaction/satisfaction_data';

    /**
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param ReportSatisfactionSummaryQuery $query
     * @return mixed
     */
    public function getBy(ReportSatisfactionSummaryQuery $query)
    {
        return $this->restClient->get($this->generateApiUrl($query), 'Report\Satisfaction[]', ['satisfaction']);
    }

    /**
     * @param ReportSatisfactionSummaryQuery $query
     * @return string
     */
    private function generateApiUrl(ReportSatisfactionSummaryQuery $query)
    {
        return sprintf ('%s?%s', self::API_ENDPOINT, http_build_query([
                'fromDate' => $query->getStartDate(),
                'toDate' => $query->getEndDate(),
                'orderBy' => $query->getOrderBy(),
                'order' => $query->getSortOrder()
            ])
        );
    }
}
