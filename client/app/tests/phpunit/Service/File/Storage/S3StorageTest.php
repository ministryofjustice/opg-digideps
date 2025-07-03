<?php

namespace App\Service\File\Storage;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class S3StorageTest extends TestCase
{
    use ProphecyTrait;

    private string $fileContent;

    public function setUp(): void
    {
        $this->fileContent = 'FILE-CONTENT-'.microtime(1);
    }

    private function generateAwsResult($statusCode, $data = [], $body = '')
    {
        $args = array_merge(['@metadata' => ['statusCode' => $statusCode]], $data);
        if (!empty($body)) {
            $args['Body'] = $body;
        }

        return new Result($args);
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

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        $object->store($key, $fileContent);
        $this->assertEquals($fileContent, $object->retrieve($key));
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

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException(FileUploadFailedException::class);
        $object->store($key, $fileContent);
    }

    public function testRemoveFromS3NoErrors()
    {
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';

        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key,
            ]
        )->andReturn(
            $this->generateAwsResult(
                200,
                [
                    'Versions' => [
                        0 => [
                            'Key' => $key,
                            'VersionId' => 'testVersionId_1',
                        ],
                        1 => [
                            'Key' => $key,
                            'VersionId' => 'testVersionId_2',
                        ],
                    ],
                ]
            )
        );

        $awsClient->shouldReceive('deleteObjects')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Delete' => [
                    'Objects' => [
                        ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                        ['Key' => $key, 'VersionId' => 'testVersionId_2'],
                    ],
                ],
            ]
        )->andReturn($this->generateAwsResult(200));

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $result = $object->removeFromS3($key);
        $this->assertEquals(
            [
                ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                ['Key' => $key, 'VersionId' => 'testVersionId_2'],
            ],
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithErrors()
    {
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';

        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key,
            ]
        )->andReturn(
            $this->generateAwsResult(
                200,
                [
                    'Versions' => [
                        0 => [
                            'Key' => $key,
                            'VersionId' => 'testVersionId_1',
                        ],
                        1 => [
                            'Key' => $key,
                            'VersionId' => 'testVersionId_2',
                        ],
                    ],
                ]
            )
        );

        $awsClient->shouldReceive('deleteObjects')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Delete' => [
                    'Objects' => [
                        ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                        ['Key' => $key, 'VersionId' => 'testVersionId_2'],
                    ],
                ],
            ]
        )->andReturn(
            $this->generateAwsResult(
                200,
                [
                    'Errors' => [
                        ['Key' => $key, 'VersionId' => 'testVersionId_1', 'Code' => 'AccessDenied', 'Message' => 'Access Denied.'],
                        ['Key' => $key, 'VersionId' => 'testVersionId_2', 'Code' => 'AccessDenied', 'Message' => 'Access Denied.'],
                    ],
                ]
            )
        );

        /** @var LoggerInterface */
        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException(\RuntimeException::class);

        $result = $object->removeFromS3($key);
        $this->assertEquals(
            [
                ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                ['Key' => $key, 'VersionId' => 'testVersionId_2'],
            ],
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithKeyNotFound()
    {
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';

        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key,
            ]
        )->andReturn(
            $this->generateAwsResult(404)
        );

        $awsClient->shouldNotReceive('deleteObjects');

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException(\RuntimeException::class);

        $result = $object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithNoKey()
    {
        $key = '';

        $awsClient = m::mock(S3Client::class);

        $this->expectException(\RuntimeException::class);

        $awsClient->shouldNotReceive('deleteObjects');

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $result = $object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WhenS3NotWorking()
    {
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';

        $awsClient = m::mock(S3Client::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key,
            ]
        )->andReturn(
            new AwsException('AWS is down', new Command('listObjectVersions'), ['code' => 500])
        );

        $awsClient->shouldNotReceive('deleteObjects');

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $object = new ClientS3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException('RuntimeException');

        $result = $object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRetrieveFromS3WhenNoSuchKey()
    {
        $key = 'nonExistentFile.png';

        /** @var ObjectProphecy|S3Client $awsClient */
        $awsClient = self::prophesize(S3Client::class);
        $s3Exception = new S3Exception(
            'The specified key does not exist.',
            new Command('getObject'),
            ['code' => 'NoSuchKey']
        );

        $awsClient->getObject(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrow($s3Exception);

        $logger = self::prophesize(LoggerInterface::class);

        $object = new ClientS3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(FileNotFoundException::class);

        $object->retrieve($key);
    }

    public function testRetrieveFromS3WhenAccessDenied()
    {
        $key = 'nonExistentFile.png';

        /** @var ObjectProphecy|S3Client $awsClient */
        $awsClient = self::prophesize(S3Client::class);
        $s3Exception = new S3Exception(
            'Access Denied.',
            new Command('getObject'),
            ['code' => 'AccessDenied']
        );

        $awsClient->getObject(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrow($s3Exception);

        $logger = self::prophesize(LoggerInterface::class);

        $object = new ClientS3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(FileNotFoundException::class);

        $object->retrieve($key);
    }

    public function testRetrieveFromS3NotMissingFileError()
    {
        $key = 'nonExistentFile.png';

        /** @var ObjectProphecy|S3Client $awsClient */
        $awsClient = self::prophesize(S3Client::class);
        $s3Exception = new S3Exception(
            'Some other error message',
            new Command('getObject'),
            ['code' => 'InvalidRequest']
        );

        $awsClient->getObject(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrow($s3Exception);

        $logger = self::prophesize(LoggerInterface::class);

        $object = new ClientS3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(S3Exception::class);

        $object->retrieve($key);
    }

    private function createMockStream(string $content): Stream
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        return new Stream($stream);
    }
}
