<?php

namespace App\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

abstract class ResponseComparer
{
    abstract public function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse): array;

    abstract public function getSqlStatement(): string;

    abstract public function getRoute(): string;
}
