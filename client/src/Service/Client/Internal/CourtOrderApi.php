<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class CourtOrderApi
{
    private const SEARCH_COURT_ORDERS = 'court-order/search-all?';

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
        return $this->restClient->get(self::SEARCH_COURT_ORDERS.http_build_query($filters), 'array');
    }
}
