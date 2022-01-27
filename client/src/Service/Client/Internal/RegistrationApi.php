<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegistrationApi
{
    private HttpClientInterface $registrationApiClient;

    public function __construct(HttpClientInterface $registrationApiClient)
    {
        $this->registrationApiClient = $registrationApiClient;
    }

    public function getMyRequestInfo()
    {
        $response = $this->registrationApiClient->request('GET', '/');

        return $response->getContent();
    }
}
