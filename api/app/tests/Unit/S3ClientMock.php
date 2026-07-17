<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit;

use Aws\Result;
use Aws\S3\S3Client;

// because S3Client uses magic methods which phpunit can't mock
class S3ClientMock extends S3Client
{
    public function getObject(array $args): Result
    {
        return new Result();
    }
}
