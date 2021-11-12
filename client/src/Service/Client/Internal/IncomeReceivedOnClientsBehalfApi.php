<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class IncomeReceivedOnClientsBehalfApi
{
    const DELETE_ENDPOINT = '%s/income-type/delete/%s';

    private RestClient $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function deleteIncomeType(string $reportOrNdr, string $incomeTypeId)
    {
        $this->restClient->delete(sprintf(self::DELETE_ENDPOINT, $reportOrNdr, $incomeTypeId));
    }
}
