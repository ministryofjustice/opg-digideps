<?php

declare(strict_types=1);

namespace App\Mapper\UserResearchResponse;

use App\Mapper\DateRangeQuery;
use App\Service\Client\Internal\UserResearchApi;
use App\Service\Client\RestClient;

class UserResearchResponseSummaryMapper
{
    /**
     * @param RestClient $restClient
     */
    public function __construct(private UserResearchApi $userResearchApi)
    {
    }

    /**
     * @return mixed
     */
    public function getBy(DateRangeQuery $query)
    {
        return $this->userResearchApi->getUserResearchResponseData($query);
    }
}
