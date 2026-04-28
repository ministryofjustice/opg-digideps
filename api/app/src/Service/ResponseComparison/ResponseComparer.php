<?php

namespace OPG\Digideps\Backend\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

abstract class ResponseComparer
{
    abstract public function compare(
        ResponseInterface $legacyResponse,
        ResponseInterface $newResponse,
        string $baseUrl,
        callable $getApiResponse
    ): array;

    abstract public function getSqlStatement(string $userIds): string;

    abstract public function getRoute(): string;
}
