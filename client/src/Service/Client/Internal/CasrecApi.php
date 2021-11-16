<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClient;

class CasrecApi
{
    public function __construct(private RestClient $restClient)
    {
    }

    /**
     * @return bool
     */
    public function clientHasCoDeputies(string $caseNumber)
    {
        return $this->restClient->get(sprintf('/casrec/clientHasCoDeputies/%s', $caseNumber), 'array');
    }
}
