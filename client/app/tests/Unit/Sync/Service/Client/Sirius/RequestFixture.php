<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Sync\Service\Client\Sirius;

use League\OpenAPIValidation\PSR7\OperationAddress;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class RequestFixture
{
    public function __construct(
        public ResponseInterface $response,
        public string $method,
        public string $path,
        public string $uri,
        public array $headers,
        public string $body,
    ) {
    }
}
