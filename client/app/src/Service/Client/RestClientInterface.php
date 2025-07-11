<?php

declare(strict_types=1);

namespace App\Service\Client;

interface RestClientInterface
{
    /**
     * Error Messages.
     */
    public const ERROR_NO_SUCCESS = 'Endpoint failed with message %s';
    public const ERROR_FORMAT = 'Cannot decode endpoint response';

    public function post(string $endpoint, mixed $data, array $jmsGroups = [], $expectedResponseType = 'array');

    public function put(string $endpoint, mixed $data, array $jmsGroups = []);

    public function delete(string $endpoint): ?array;
}
