<?php

declare(strict_types=1);

namespace App\Service\Client\Slack;

use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory as JoliClientFactory;

class ClientFactory
{
    /**
     * Wrapping JoliCode\Slack\ClientFactory::create() to enable testing
     * in unit tests.
     */
    public function createClient(string $apiToken): Client
    {
        return JoliClientFactory::create($apiToken);
    }
}
