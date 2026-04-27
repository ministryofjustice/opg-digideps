<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Client\Internal;

use OPG\Digideps\Frontend\Service\Client\RestClient;

class PreRegistrationApi
{
    public function __construct(private RestClient $restClient)
    {
    }

    public function deleteAll(): void
    {
        $this->restClient->delete('/pre-registration/delete');
    }

    public function count()
    {
        return $this->restClient->get('/pre-registration/count', 'array');
    }

    public function verify(mixed $clientData)
    {
        return $this->restClient->apiCall('post', 'pre-registration/verify', $clientData, 'array', []);
    }
}
