<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Mapper\UserResearchResponse;

use OPG\Digideps\Frontend\Mapper\DateRangeQuery;
use OPG\Digideps\Frontend\Service\Client\Internal\UserResearchApi;
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
