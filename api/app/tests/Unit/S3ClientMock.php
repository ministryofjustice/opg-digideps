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

    public function putObject(array $args): Result
    {
        return new Result();
    }

    public function putObjectTagging(array $args): Result
    {
        return new Result();
    }

    public function getObjectTagging(array $args): Result
    {
        return new Result();
    }

    public function deleteObject(array $args): Result
    {
        return new Result();
    }

    public function deleteObjects(array $args): Result
    {
        return new Result();
    }

    public function listObjectVersions(array $args): Result
    {
        return new Result();
    }
}
