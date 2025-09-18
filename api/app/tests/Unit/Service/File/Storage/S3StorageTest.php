<?php

declare(strict_types=1);

namespace App\Service\File\Storage;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Psr7\Stream;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

final class S3StorageTest extends TestCase
{
    use ProphecyTrait;

    private string $fileContent;
    private S3Storage $object;

    public function setUp(): void
    {
        $this->fileContent = 'FILE-CONTENT-'.microtime(true);
    }

    public function testUploadDownloadDeleteTextContent(): void
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(true);

        $awsClient = m::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('putObject')
            ->with(m::type('array'))
            ->once()
            ->andReturn($this->generateAwsResult(200));

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(true);

        $awsClient->shouldReceive('getObjectTagging')
            ->with(m::type('array'))
            ->once()
            ->andReturn(
                [
                    'TagSet' => [
                        [
                            'Key' => 'someKey',
                            'Value' => 'someValue',
                        ],
                    ],
                    'VersionId' => 'someVersionId',
                ]
            );

        $awsClient->shouldReceive('putObjectTagging')
            ->with(m::type('array'))
            ->once()
            ->andReturn(['VersionId' => 'someVersionId']);

        $awsClient->shouldReceive('deleteObject')
            ->once()
            ->andReturn($this->generateAwsResult(204));

        // Initial call to getObject returns fileContent
        $awsClient->shouldReceive('getObject')
            ->with(m::type('array'))
            ->andReturn($this->generateAwsResult(200, [], $this->createMockStream($this->fileContent)));

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // store
        $ret = $this->object->store($key, $this->fileContent);
        $this->assertEquals(200, $ret->toArray()['@metadata']['statusCode']);

        // retrieve
        $this->assertEquals($this->fileContent, $this->object->retrieve($key));

        // delete
        $ret = $this->object->delete($key);
        $this->assertEquals(204, $ret->toArray()['@metadata']['statusCode']);

        // Subsequent call to getObject requires a new mock to allow getObject to throw Exception since file removed
        $awsClient = m::mock(S3ClientInterface::class);
        $awsClient->shouldReceive('getObject')->with(
            m::type('array')
        )->andThrow(FileNotFoundException::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // try retrieve after deletion (Exception expected)
        $this->expectException(FileNotFoundException::class);
        $this->object->retrieve($key);
    }

    private function generateAwsResult(int $statusCode, array $data = [], $body = ''): Result
    {
        $args = array_merge(['@metadata' => ['statusCode' => $statusCode]], $data);
        if (!empty($body)) {
            $args['Body'] = $body;
        }

        return new Result($args);
    }

    public function testSuccessfulUploadBinaryContent(): void
    {
        /** @var S3ClientInterface */

        $awsClient = m::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));
        $awsClient->shouldReceive('getObject')->with(
            m::type('array')
        )->andReturn($this->generateAwsResult(200, [], $this->createMockStream(file_get_contents(__DIR__.'/cat.jpg'))));

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(true);

        /** @var LoggerInterface */
        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(true).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        $this->object->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->object->retrieve($key));
    }

    public function testFailedUploadBinaryContent(): void
    {
        /** @var S3ClientInterface */

        $awsClient = m::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));

        $awsClient->shouldReceive('waitUntil')->andReturn($awsClient);;
        $awsClient->shouldReceive('doesObjectExistV2')->andReturn(false);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(true).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        /** @var LoggerInterface */
        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs([
            'error',
            'Failed to upload file to S3. Filename: '. $key
        ]);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException(FileUploadFailedException::class);
        $this->object->store($key, $fileContent);
    }

    public function testRemoveFromS3NoErrors(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(true).'.png';

        $awsClient = m::mock(S3ClientInterface::class);

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

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            [
                ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                ['Key' => $key, 'VersionId' => 'testVersionId_2'],
            ],
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithErrors(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(true).'.png';

        $awsClient = m::mock(S3ClientInterface::class);

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

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException('RuntimeException', 'Could not remove file');

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            [
                ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                ['Key' => $key, 'VersionId' => 'testVersionId_2'],
            ],
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithKeyNotFound(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(true).'.png';

        /** @var S3ClientInterface */
        $awsClient = m::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key,
            ]
        )->andReturn(
            $this->generateAwsResult(404)
        );

        $awsClient->shouldNotReceive('deleteObjects')->never();

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException('RuntimeException', 'Could not remove file: No results returned');

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithNoKey(): void
    {
        $key = '';

        $awsClient = m::mock(S3ClientInterface::class);

        $this->expectException('RuntimeException', 'Could not remove file');

        $awsClient->shouldNotReceive('deleteObjects')->never();

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WhenS3NotWorking(): void
    {
        $key = 'storagetest-upload-download-delete'.microtime(true).'.png';

        $awsClient = m::mock(S3ClientInterface::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key,
            ]
        )->andReturn(
            new AwsException('AWS is down', new Command('listObjectVersions'), ['code' => 500])
        );

        $awsClient->shouldNotReceive('deleteObjects')->never();

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->expectException('RuntimeException', 'Could not remove file');

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRetrieveFromS3WhenNoSuchKey(): void
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

        $this->object = new S3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(FileNotFoundException::class, "Cannot find file with reference {$key}");

        $this->object->retrieve($key);
    }

    public function testRetrieveFromS3WhenAccessDenied(): void
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

        $this->object = new S3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(FileNotFoundException::class, "Cannot find file with reference {$key}");

        $this->object->retrieve($key);
    }

    public function testRetrieveFromS3NotMissingFileError(): void
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

        $this->object = new S3Storage($awsClient->reveal(), 'unit_test_bucket', $logger->reveal());

        $this->expectException(S3Exception::class, 'Some other error message');

        $this->object->retrieve($key);
    }

    private function createMockStream(string $content): Stream
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        return new Stream($stream);
    }
}
