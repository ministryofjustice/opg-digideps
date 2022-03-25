<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class MoneyReceivedOnClientsBehalfApi
{
    const DELETE_ENDPOINT = '%s/money-type/delete/%s';

    private RestClient $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function deleteMoneyType(string $reportOrNdr, string $moneyTypeId)
    {
        $this->restClient->delete(sprintf(self::DELETE_ENDPOINT, $reportOrNdr, $moneyTypeId));
    }
}
