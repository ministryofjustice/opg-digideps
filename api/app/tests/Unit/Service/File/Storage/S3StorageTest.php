<?php

namespace App\Tests\Unit\Service\File\Storage;

use App\Service\File\Storage\FileNotFoundException;
use App\Service\File\Storage\FileUploadFailedException;
use App\Service\File\Storage\S3Storage;
use Aws\Command;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class S3StorageTest extends TestCase
{
    public function tearDown(): void
    {
        \Mockery::close();
    }

    private function generateAwsResult(int $statusCode, array $data = [], ?Stream $body = null): Result
    {
        $args = array_merge(['@metadata' => ['statusCode' => $statusCode]], $data);

        if (!is_null($body)) {
            $args['Body'] = $body;
        }

        return new Result($args);
    }

    public function testTagForDeletionS3ObjectAlreadyMarkedForDeletion(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(1);

        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('getObjectTagging')
            ->andReturn(['TagSet' => [['Key' => 'Purge', 'Value' => '1']]]);

        // we don't need to put the tag as it's already set on the object
        $awsClient->shouldNotReceive('putObjectTagging');

        $mockLogger = \Mockery::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log');

        // sut
        $sut = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // test
        $ret = $sut->tagForDeletion($key);

        // assert
        $this->assertTrue($ret);
    }

    public function testTagForDeletionMissingObject(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(1);

        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('getObjectTagging')
            ->andThrow(self::createMock(S3Exception::class));

        // sut
        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        // test
        $ret = $sut->tagForDeletion($key);

        // assert
        $this->assertFalse($ret);
    }

    public function testTagForDeletionPutTagsFails(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(1);

        $awsClient = \Mockery::mock(S3ClientInterface::class);
        $awsClient->shouldReceive('getObjectTagging')
            ->andReturn(['TagSet' => []]);
        $awsClient->shouldReceive('putObjectTagging')
            ->andThrow(self::createMock(S3Exception::class));

        // sut
        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        // test
        $ret = $sut->tagForDeletion($key);

        // assert
        $this->assertFalse($ret);
    }

    public function testTagForDeletion(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(1);

        $currentTags = [
            [
                'Key' => 'someKey',
                'Value' => 'someValue',
            ],
        ];

        $expectedTags = array_merge($currentTags, [['Key' => 'Purge', 'Value' => '1']]);

        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('getObjectTagging')
            ->andReturn(['TagSet' => $currentTags]);

        $awsClient->shouldReceive('putObjectTagging')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key, 'Tagging' => ['TagSet' => $expectedTags]]);

        // sut
        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        // test
        $ret = $sut->tagForDeletion($key);

        // assert
        $this->assertTrue($ret);
    }

    public function testSuccessfulUploadBinaryContent()
    {
        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $fileContent = 'cat.jpg';

        $mockStream = fopen('php://memory', 'r+');
        fwrite($mockStream, $fileContent);
        rewind($mockStream);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));
        $awsClient->shouldReceive('getObject')->andReturn(
            $this->generateAwsResult(
                200,
                [],
                new Stream($mockStream)
            )
        );

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(true);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';

        $sut->store($key, $fileContent);

        $this->assertEquals($fileContent, $sut->retrieve($key));
    }

    public function testFailedUploadBinaryContent()
    {
        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(false);

        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        $this->expectException(FileUploadFailedException::class);

        $sut->store($key, $fileContent);
    }

    public function testRetrieveFromS3WhenAccessDenied()
    {
        $key = 'nonExistentFile.png';

        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $s3Exception = new S3Exception(
            'Access Denied.',
            new Command('getObject'),
            ['code' => 'AccessDenied']
        );

        $awsClient->shouldReceive('getObject')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])
            ->andThrow($s3Exception);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        $this->expectException(FileNotFoundException::class);

        $sut->retrieve($key);
    }

    public function testRetrieveFromS3NotMissingFileError()
    {
        $key = 'nonExistentFile.png';

        $awsClient = \Mockery::mock(S3ClientInterface::class);

        $s3Exception = new S3Exception(
            'Some other error message',
            new Command('getObject'),
            ['code' => 'InvalidRequest']
        );

        $awsClient->shouldReceive('getObject')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])
            ->andThrow($s3Exception);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', self::createMock(LoggerInterface::class));

        $this->expectException(S3Exception::class);

        $sut->retrieve($key);
    }
}
