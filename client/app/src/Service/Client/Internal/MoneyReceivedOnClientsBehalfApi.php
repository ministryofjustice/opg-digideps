<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class MoneyReceivedOnClientsBehalfApi
{
    const string DELETE_ENDPOINT = 'report/money-type/delete/%s';

    private RestClient $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function deleteMoneyType(string $moneyTypeId): void
    {
        $this->restClient->delete(sprintf(self::DELETE_ENDPOINT, $moneyTypeId));
    }
}
