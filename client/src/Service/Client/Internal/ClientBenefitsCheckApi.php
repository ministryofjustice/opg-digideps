<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\Report\ClientBenefitsCheck;
use App\Service\Client\RestClient;

class ClientBenefitsCheckApi
{
    const CREATE_ENDPOINT = '%s/client-benefits-check';
    const EXISTING_ENDPOINT = '%s/client-benefits-check/%s';

    public function __construct(private RestClient $restClient)
    {
    }

    public function post(ClientBenefitsCheckInterface $clientBenefitsCheck)
    {
        $reportOrNdr = $clientBenefitsCheck instanceof ClientBenefitsCheck ? 'report' : 'ndr';
        $this->restClient->post(
            sprintf(self::CREATE_ENDPOINT, $reportOrNdr),
            $clientBenefitsCheck,
        );
    }

    public function put(ClientBenefitsCheckInterface $clientBenefitsCheck)
    {
        $reportOrNdr = $clientBenefitsCheck instanceof ClientBenefitsCheck ? 'report' : 'ndr';
        $this->restClient->put(
            sprintf(self::EXISTING_ENDPOINT, $reportOrNdr, $clientBenefitsCheck->getId()),
            $clientBenefitsCheck
        );
    }
}
