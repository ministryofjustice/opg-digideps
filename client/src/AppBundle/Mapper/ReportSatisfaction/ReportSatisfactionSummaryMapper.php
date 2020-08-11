<?php

namespace AppBundle\Mapper\ReportSatisfaction;

use AppBundle\Service\Client\RestClient;

class ReportSatisfactionSummaryMapper
{
    /** @var RestClient */
    private $restClient;

    /** @var string */
    const API_ENDPOINT = '/report-submission/satisfaction_data';

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

        $resp = $this->restClient->get($this->generateApiUrl($query), 'Report\Satisfaction[]', ['satisfaction']);
        file_put_contents('php://stderr', print_r(" THIS IS OUR MAPPER RESPONSE \n\n\n\n\n", TRUE));
        file_put_contents('php://stderr', print_r($resp, TRUE));
        file_put_contents('php://stderr', print_r(" THIS IS OUR MAPPER RESPONSE END \n\n\n\n\n", TRUE));
        return $resp;

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
