<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Report\ClientBenefitsCheck;
use App\Service\Client\RestClient;

class ClientBenefitCheckApi
{
    const CREATE_ENDPOINT = '/client-benefits-check';
    const EXISTING_ENDPOINT = '/client-benefits-check/%s';

    private RestClient $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function post(ClientBenefitsCheck $clientBenefitsCheck)
    {
        $this->restClient->post(
            self::CREATE_ENDPOINT,
            $clientBenefitsCheck
        );
    }

    public function put(ClientBenefitsCheck $clientBenefitsCheck)
    {
        $this->restClient->put(
            sprintf(self::EXISTING_ENDPOINT, $clientBenefitsCheck->getId()),
            $clientBenefitsCheck
        );
    }
}
