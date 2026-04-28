<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client\Internal;

use OPG\Digideps\Frontend\Entity\ClientBenefitsCheckInterface;
use OPG\Digideps\Frontend\Service\Client\RestClient;

class ClientBenefitsCheckApi
{
    private const string CREATE_ENDPOINT = 'report/client-benefits-check';
    private const string EXISTING_ENDPOINT = 'report/client-benefits-check/%s';

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
