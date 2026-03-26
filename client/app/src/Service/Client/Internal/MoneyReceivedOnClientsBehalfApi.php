<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class MoneyReceivedOnClientsBehalfApi
{
    << << << < Updated upstream
    const string DELETE_ENDPOINT = 'report/money-type/delete/%s';
    === === =
    const string DELETE_ENDPOINT = '%s/money-type/delete/%s';
    >> >> >> > Stashed changes

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
