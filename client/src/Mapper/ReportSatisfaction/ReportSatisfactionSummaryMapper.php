<?php declare(strict_types=1);

namespace App\Mapper\ReportSatisfaction;

use App\Service\Client\RestClient;

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
        $queryArray = [
            'orderBy' => $query->getOrderBy(),
            'order' => $query->getSortOrder()
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
