<?php

declare(strict_types=1);

namespace App\Mapper\UserResearchResponse;

use App\Mapper\DateRangeQuery;
use App\Service\Client\Internal\UserResearchApi;
use Psr\Http\Message\StreamInterface;

readonly class UserResearchResponseSummaryMapper
{
    public function __construct(
        private UserResearchApi $userResearchApi
    ) {
    }

    public function getBy(DateRangeQuery $query): StreamInterface
    {
        return $this->userResearchApi->getUserResearchResponseData($query);
    }
}
