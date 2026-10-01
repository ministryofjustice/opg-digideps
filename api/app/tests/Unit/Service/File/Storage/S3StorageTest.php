<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\File\Storage;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Psr7\Stream;
use OPG\Digideps\Backend\Service\File\Storage\FileNotFoundException;
use OPG\Digideps\Backend\Service\File\Storage\FileUploadFailedException;
use OPG\Digideps\Backend\Service\File\Storage\S3Storage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tests\OPG\Digideps\Backend\Unit\S3ClientMock;

final class S3StorageTest extends TestCase
{
    private string $fileContent;
    private S3Storage $object;

    public function setUp(): void
    {
        $this->fileContent = 'FILE-CONTENT-' . microtime(true);
    }

    public function testUploadDownloadDeleteTextContent(): void
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(true);

        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())
            ->method('putObject')
            ->willReturn($this->generateAwsResult(200));

        $awsClient->expects(self::once())->method('waitUntil')->willReturn($awsClient);
        $awsClient->expects(self::once())->method('doesObjectExistV2')->willReturn(true);

        $mockObjectTagging = self::createMock(Result::class);
        $mockObjectTagging->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'TagSet' => [
                    [
                        'Key' => 'someKey',
                        'Value' => 'someValue',
                    ],
                ],
                'VersionId' => 'someVersionId',
            ]);

        $awsClient->expects(self::once())
            ->method('getObjectTagging')
            ->willReturn($mockObjectTagging);

        $awsClient->expects(self::once())
            ->method('putObjectTagging')
            ->willReturn($this->generateAwsResult(200, ['VersionId' => 'someVersionId']));

        $awsClient->expects(self::once())
            ->method('deleteObject')
            ->willReturn($this->generateAwsResult(204));

        // Initial call to getObject returns fileContent
        $awsClient->expects(self::once())
            ->method('getObject')
            ->willReturn($this->generateAwsResult(200, [], $this->createMockStream($this->fileContent)));

        $stubLogger = self::createStub(LoggerInterface::class);
        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        // store
        $ret = $this->object->store($key, $this->fileContent);
        $this->assertEquals(200, $ret->toArray()['@metadata']['statusCode']);

        // retrieve
        $this->assertEquals($this->fileContent, $this->object->retrieve($key));

        // delete
        $ret = $this->object->delete($key);
        $this->assertEquals(204, $ret->toArray()['@metadata']['statusCode']);

        // Subsequent call to getObject requires a new mock to allow getObject to throw Exception since file removed
        $awsClient = self::createMock(S3ClientMock::class);
        $awsClient->expects(self::once())
            ->method('getObject')
            ->willThrowException(new FileNotFoundException());

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

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
        /** @var S3ClientMock&MockObject $awsClient */
        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())
            ->method('putObject')
            ->willReturn($this->generateAwsResult(200));

        $awsClient->expects(self::once())
            ->method('getObject')
            ->willReturn($this->generateAwsResult(200, [], $this->createMockStream(file_get_contents(__DIR__ . '/cat.jpg'))));

        $awsClient->expects(self::once())->method('waitUntil')->willReturn($awsClient);
        $awsClient->expects(self::once())->method('doesObjectExistV2')->willReturn(true);

        $stubLogger = self::createStub(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $this->object->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->object->retrieve($key));
    }

    public function testFailedUploadBinaryContent(): void
    {
        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())->method('putObject')->willReturn($this->generateAwsResult(200));

        $awsClient->expects(self::once())->method('waitUntil')->willReturn($awsClient);
        ;
        $awsClient->expects(self::once())->method('doesObjectExistV2')->willReturn(false);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $stubLogger = self::createMock(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        $this->expectException(FileUploadFailedException::class);
        $this->object->store($key, $fileContent);
    }

    public function testRemoveFromS3NoErrors(): void
    {
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';

        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())
            ->method('listObjectVersions')
            ->with(
                [
                    'Bucket' => 'unit_test_bucket',
                    'Prefix' => $key,
                ]
            )
            ->willReturn(
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

        $awsClient->expects(self::once())
            ->method('deleteObjects')
            ->with(
                [
                    'Bucket' => 'unit_test_bucket',
                    'Delete' => [
                        'Objects' => [
                            ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                            ['Key' => $key, 'VersionId' => 'testVersionId_2'],
                        ],
                    ],
                ]
            )
            ->willReturn($this->generateAwsResult(200));

        $mockLogger = self::createMock(LoggerInterface::class);
        $mockLogger->expects(self::once())->method('log');

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
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';

        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())
            ->method('listObjectVersions')
            ->with(
                [
                    'Bucket' => 'unit_test_bucket',
                    'Prefix' => $key,
                ]
            )
            ->willReturn(
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

        $awsClient->expects(self::once())
            ->method('deleteObjects')
            ->with(
                [
                    'Bucket' => 'unit_test_bucket',
                    'Delete' => [
                        'Objects' => [
                            ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                            ['Key' => $key, 'VersionId' => 'testVersionId_2'],
                        ],
                    ],
                ]
            )->willReturn(
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

        $stubLogger = self::createStub(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        $this->expectException(\RuntimeException::class);

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
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';

        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())
            ->method('listObjectVersions')
            ->with(
                [
                    'Bucket' => 'unit_test_bucket',
                    'Prefix' => $key,
                ]
            )->willReturn(
                $this->generateAwsResult(404)
            );

        $awsClient->expects(self::never())->method('deleteObjects');

        $stubLogger = self::createStub(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        $this->expectException(\RuntimeException::class);

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithNoKey(): void
    {
        $key = '';

        $awsClient = self::createMock(S3ClientMock::class);

        $this->expectException(\RuntimeException::class);

        $awsClient->expects(self::never())->method('deleteObjects');

        $stubLogger = self::createStub(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WhenS3NotWorking(): void
    {
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';

        $awsClient = self::createMock(S3ClientMock::class);

        $awsClient->expects(self::once())
            ->method('listObjectVersions')
            ->with(
                [
                    'Bucket' => 'unit_test_bucket',
                    'Prefix' => $key,
                ]
            )->willThrowException(
                new AwsException('AWS is down', new Command('listObjectVersions'), ['code' => 500])
            );

        $awsClient->expects(self::never())->method('deleteObjects');

        $stubLogger = self::createStub(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $stubLogger);

        $this->expectException(\RuntimeException::class);

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRetrieveFromS3WhenNoSuchKey(): void
    {
        $key = 'nonExistentFile.png';

        $awsClient = self::createMock(S3ClientMock::class);
        $s3Exception = new S3Exception(
            'The specified key does not exist.',
            new Command('getObject'),
            ['code' => 'NoSuchKey']
        );

        $awsClient->expects(self::once())
            ->method('getObject')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])
            ->willThrowException($s3Exception);

        $logger = self::createMock(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $logger);

        $this->expectException(FileNotFoundException::class);

        $this->object->retrieve($key);
    }

    public function testRetrieveFromS3WhenAccessDenied(): void
    {
        $key = 'nonExistentFile.png';

        $awsClient = self::createMock(S3ClientMock::class);
        $s3Exception = new S3Exception(
            'Access Denied.',
            new Command('getObject'),
            ['code' => 'AccessDenied']
        );

        $awsClient->expects(self::once())
            ->method('getObject')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])
            ->willThrowException($s3Exception);

        $logger = self::createMock(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $logger);

        $this->expectException(FileNotFoundException::class);

        $this->object->retrieve($key);
    }

    public function testRetrieveFromS3NotMissingFileError(): void
    {
        $key = 'nonExistentFile.png';

        $awsClient = self::createMock(S3ClientMock::class);
        $s3Exception = new S3Exception(
            'Some other error message',
            new Command('getObject'),
            ['code' => 'InvalidRequest']
        );

        $awsClient->expects(self::once())
            ->method('getObject')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])
            ->willThrowException($s3Exception);

        $logger = self::createMock(LoggerInterface::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $logger);

        $this->expectException(S3Exception::class);

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
