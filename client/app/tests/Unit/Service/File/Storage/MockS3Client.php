<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File\Storage;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\S3ClientInterface;

abstract class MockS3Client implements S3ClientInterface
{
    abstract public function deleteObject(array $args): Result;
    abstract public function deleteObjects(array $args): Result;
    abstract public function getObject(array $args): Result;
    abstract public function listObjectVersions(array $args): Result|AwsException;
    abstract public function putObject(array $args): Result;
    abstract public function getObjectTagging(array $args): array;
}
