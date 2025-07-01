<?php

namespace App\Service\File\Storage;

use Aws\Command;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class S3StorageTest extends TestCase
{
    use ProphecyTrait;

    private S3Storage $sut;

    private function generateAwsResult($statusCode, $data = [], $body = '')
    {
        $args = array_merge(['@metadata' => ['statusCode' => $statusCode]], $data);
        if (!empty($body)) {
            $args['Body'] = $body;
        }

        return new Result($args);
    }

    public function testTagForDeletion()
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(1);

        $currentTags = [
            'TagSet' => [
                [
                    'Key' => 'someKey',
                    'Value' => 'someValue',
                ],
            ],
        ];

        $expectedTags = array_merge($currentTags, ['Purge' => 1]);

        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('getObjectTagging')
            ->with(m::type('array'))
            ->once()
            ->andReturn($currentTags);

        $awsClient->shouldReceive('putObjectTagging')
            ->with($expectedTags)
            ->once();

        $mockLogger = m::mock(LoggerInterface::class);

        // sut
        $this->sut = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // test
        $ret = $this->sut->tagForDeletion($key);

        // assert
        $this->assertTrue($ret);
    }

    public function testSuccessfulUploadBinaryContent()
    {
        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));
        $awsClient->shouldReceive('getObject')->with(
            m::type('array')
        )->andReturn($this->generateAwsResult(200, [], $this->createMockStream(file_get_contents(__DIR__.'/cat.jpg'))));

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(true);

        /** @var LoggerInterface */
        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->sut = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        $this->sut->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->sut->retrieve($key));
    }

    public function testFailedUploadBinaryContent()
    {
        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(false);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        /** @var LoggerInterface */
        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->sut = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException(FileUploadFailedException::class);
        $this->sut->store($key, $fileContent);
    }

    public function testRetrieveFromS3WhenAccessDenied()
    {
        $key = 'nonExistentFile.png';

        $awsClient = self::prophesize(S3Client::class);
        $s3Exception = new S3Exception(
            'Access Denied.',
            new Command('getObject'),
            ['code' => 'AccessDenied']
        );

        $awsClient->getObject(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrow($s3Exception);

        $logger = self::prophesize(LoggerInterface::class);

        $this->sut = new S3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(FileNotFoundException::class);

        $this->sut->retrieve($key);
    }

    public function testRetrieveFromS3NotMissingFileError()
    {
        $key = 'nonExistentFile.png';

        $awsClient = self::prophesize(S3Client::class);
        $s3Exception = new S3Exception(
            'Some other error message',
            new Command('getObject'),
            ['code' => 'InvalidRequest']
        );

        $awsClient->getObject(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrow($s3Exception);

        $logger = self::prophesize(LoggerInterface::class);

        $this->sut = new S3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(S3Exception::class);

        $this->sut->retrieve($key);
    }

    private function createMockStream(string $content): Stream
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        return new Stream($stream);
    }
}
