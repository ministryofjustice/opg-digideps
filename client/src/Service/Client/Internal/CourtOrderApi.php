<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class CourtOrderApi
{
    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @return array
     */
    public function searchForCourtOrders(array $filters)
    {
        return $this->restClient->get('court-order/search-all?'.http_build_query($filters), 'array');
    }
}
