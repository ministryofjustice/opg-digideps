<?php

namespace App\Service\ResponseComparison;

use Psr\Http\Message\ResponseInterface;

abstract class ResponseComparer
{
    abstract protected function compare(ResponseInterface $legacyResponse, ResponseInterface $newResponse): bool;

    abstract protected function getSqlStatement(): string;

    abstract public function getRoute(): string;
}
