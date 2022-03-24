<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class CasrecApi
{
    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @return bool
     */
    public function clientHasCoDeputies(string $caseNumber)
    {
        return $this->restClient->get(sprintf('/casrec/clientHasCoDeputies/%s', $caseNumber), 'array');
    }

    public function deleteAll()
    {
        return $this->restClient->delete('casrec/delete');
    }
}
