<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class IncomeReceivedOnClientsBehalfApi
{
    const DELETE_ENDPOINT = '/income-type/delete/%s';

    public function __construct(private RestClient $restClient)
    {
    }

    public function deleteIncomeType(string $incomeTypeId)
    {
        $this->restClient->delete(sprintf(self::DELETE_ENDPOINT, $incomeTypeId));
    }
}
