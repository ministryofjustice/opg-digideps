<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client\Internal;

use OPG\Digideps\Frontend\Service\Client\RestClient;

class MoneyReceivedOnClientsBehalfApi
{
    private const string DELETE_ENDPOINT = 'report/money-type/delete/%s';

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
