<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;
use Psr\Http\Message\StreamInterface;

class PreRegistrationApi
{
    public function __construct(private RestClient $restClient)
    {
    }

    /**
     * @return bool
     */
    public function clientHasCoDeputies(string $caseNumber)
    {
        return $this->restClient->get(sprintf('/pre-registration/clientHasCoDeputies/%s', $caseNumber), 'array');
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

    /**
     * Returns a JSON-encoded string.
     */
    public function getReportTypeBasedOnSirius(string $caseNumber, string $deputyUid): string
    {
        /** @var StreamInterface $body */
        $body = $this->restClient->get(sprintf('/pre-registration/report-type/%s/%s', $caseNumber, $deputyUid), 'raw');

        return $body->getContents();
    }
}
