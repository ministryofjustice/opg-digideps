<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\ClientBenefitsCheckInterface;
use App\Service\Client\RestClient;

class ClientBenefitsCheckApi
{
    const string CREATE_ENDPOINT = 'report/client-benefits-check';
    const string EXISTING_ENDPOINT = 'report/client-benefits-check/%s';

    private RestClient $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function post(ClientBenefitsCheckInterface $clientBenefitsCheck): void
    {
        $this->restClient->post(self::CREATE_ENDPOINT, $clientBenefitsCheck);
    }

    public function put(ClientBenefitsCheckInterface $clientBenefitsCheck): void
    {
        $this->restClient->put(
            sprintf(self::EXISTING_ENDPOINT, $clientBenefitsCheck->getId()),
            $clientBenefitsCheck
        );
    }
}
