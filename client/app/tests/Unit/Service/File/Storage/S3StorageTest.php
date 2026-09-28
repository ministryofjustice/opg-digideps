<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File\Storage;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Psr7\Stream;
use OPG\Digideps\Frontend\Service\File\Storage\FileNotFoundException;
use OPG\Digideps\Frontend\Service\File\Storage\FileUploadFailedException;
use OPG\Digideps\Frontend\Service\File\Storage\S3Storage;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class S3StorageTest extends TestCase
{
    private string $fileContent;

    public function setUp(): void
    {
        $this->fileContent = 'FILE-CONTENT-' . microtime(true);
    }

    public function testUploadDownloadDeleteTextContent(): void
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(true);

        $awsClient = $this->createAwsMock();
        $awsClient->expects(self::once())
            ->method('putObject')
            ->with(new IsType(IsType::TYPE_ARRAY))
            ->willReturn($this->generateAwsResult(200));

        $awsClient->method('waitUntil')->willReturn($awsClient);
        $awsClient->method('doesObjectExistV2')->willReturn(true);

        $awsClient->expects(self::once())
            ->method('getObjectTagging')
            ->with(new IsType(IsType::TYPE_ARRAY))
            ->willReturn(
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

        $awsClient->expects(self::once())->method('deleteObject')->willReturn($this->generateAwsResult(204));

        // Initial call to getObject returns fileContent
        $awsClient->method('getObject')
            ->with(new IsType(IsType::TYPE_ARRAY))
            ->willReturn($this->generateAwsResult(200, [], $this->createMockStream($this->fileContent)));

        $mockLogger = self::createMock(LoggerInterface::class);
        $mockLogger->expects(self::atLeastOnce())->method('log');

        $storage1 = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // store
        $ret = $storage1->store($key, $this->fileContent);
        self::assertEquals(200, $ret->toArray()['@metadata']['statusCode']);

        // retrieve
        self::assertEquals($this->fileContent, $storage1->retrieve($key));

        // delete
        $ret = $storage1->delete($key);
        self::assertEquals(204, $ret->toArray()['@metadata']['statusCode']);

        $awsClient->method('getObject')
            ->with(new IsType(IsType::TYPE_ARRAY))
            ->willThrowException(new FileNotFoundException());

        $storage2 = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // try retrieve after deletion (Exception expected)
        self::expectException(FileNotFoundException::class);
        $storage2->retrieve($key);
    }

    private function generateAwsResult($statusCode, $data = [], $body = ''): Result
    {
        $args = array_merge(['@metadata' => ['statusCode' => $statusCode]], $data);
        if (!empty($body)) {
            $args['Body'] = $body;
        }

        return new Result($args);
    }

    public function testSuccessfulUploadBinaryContent(): void
    {
        $awsClient = $this->createAwsMock();

        $awsClient->method('putObject')->willReturn($this->generateAwsResult(200));
        $awsClient->method('getObject')
            ->with(new IsType(IsType::TYPE_ARRAY))
            ->willReturn(
                $this->generateAwsResult(200, [], $this->createMockStream(file_get_contents(__DIR__ . '/cat.jpg')))
            );

        $awsClient->method('waitUntil')->willReturn($awsClient);
        $awsClient->method('doesObjectExistV2')->willReturn(true);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $sut->store($key, $fileContent);
        self::assertEquals($fileContent, $sut->retrieve($key));
    }

    public function testFailedUploadBinaryContent(): void
    {
        $awsClient = $this->createAwsMock();

        $awsClient->method('putObject')->willReturn($this->generateAwsResult(200));

        $awsClient->method('waitUntil')->willReturn($awsClient);
        $awsClient->method('doesObjectExistV2')->willReturn(false);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $mockLogger = self::createMock(LoggerInterface::class);
        $mockLogger->method('log')->with(
            'error',
            'Failed to upload file to S3. Filename: ' . $key,
            ['extra' => [
                'service' => 's3-storage',
            ]]
        );

        $sut = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        self::expectException(FileUploadFailedException::class);
        $sut->store($key, $fileContent);
    }

    public function testRemoveFromS3NoErrors(): void
    {
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';

        $awsClient = $this->createAwsMock();

        $awsClient->method('listObjectVersions')
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

        $awsClient->method('deleteObjects')
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

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        $result = $sut->removeFromS3($key);
        self::assertEquals(
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

        $awsClient = $this->createAwsMock();

        $awsClient->method('listObjectVersions')
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

        $awsClient->method('deleteObjects')
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
            ->willReturn(
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

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        self::expectException('RuntimeException');
        self::expectExceptionMessage('Could not remove file');

        $result = $sut->removeFromS3($key);
        self::assertEquals(
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

        $awsClient = $this->createAwsMock();

        $awsClient->method('listObjectVersions')->with([
            'Bucket' => 'unit_test_bucket',
            'Prefix' => $key,
        ])->willReturn($this->generateAwsResult(404));

        $awsClient->expects(self::never())->method('deleteObjects');

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        self::expectException('RuntimeException');
        self::expectExceptionMessage('Could not remove file: No results returned');

        $result = $sut->removeFromS3($key);

        self::assertEquals('', $result['objectsToDelete']);
    }

    public function testRemoveFromS3WithNoKey(): void
    {
        $key = '';

        $awsClient = $this->createAwsMock();

        self::expectException('RuntimeException');
        self::expectExceptionMessage('Could not remove file');

        $awsClient->expects(self::never())->method('deleteObjects');

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        $result = $sut->removeFromS3($key);

        self::assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WhenS3NotWorking(): void
    {
        $key = 'storagetest-upload-download-delete' . microtime(true) . '.png';

        $awsClient = self::createAwsMock();

        $awsClient->method('listObjectVersions')->with([
            'Bucket' => 'unit_test_bucket',
            'Prefix' => $key,
        ])->willReturn(
            new AwsException('AWS is down', new Command('listObjectVersions'), ['code' => 500])
        );

        $awsClient->expects(self::never())->method('deleteObjects');

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        self::expectException('RuntimeException');
        self::expectExceptionMessage('Could not remove file');

        $result = $sut->removeFromS3($key);
        self::assertEquals(
            '',
            $result['objectsToDelete']
        );
    }

    public function testRetrieveFromS3WhenNoSuchKey(): void
    {
        $key = 'nonExistentFile.png';

        $awsClient = $this->createAwsMock();

        $s3Exception = new S3Exception(
            'The specified key does not exist.',
            new Command('getObject'),
            ['code' => 'NoSuchKey']
        );

        $awsClient->method('getObject')
            ->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])
            ->willThrowException($s3Exception);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        self::expectException(FileNotFoundException::class);
        self::expectExceptionMessage("Cannot find file with reference {$key}");

        $sut->retrieve($key);
    }

    public function testRetrieveFromS3WhenAccessDenied(): void
    {
        $key = 'nonExistentFile.png';

        $awsClient = $this->createAwsMock();
        $s3Exception = new S3Exception(
            'Access Denied.',
            new Command('getObject'),
            ['code' => 'AccessDenied']
        );

        $awsClient->method('getObject')->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrowException($s3Exception);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', new NullLogger());

        self::expectException(FileNotFoundException::class);
        self::expectExceptionMessage("Cannot find file with reference {$key}");

        $sut->retrieve($key);
    }

    public function testRetrieveFromS3NotMissingFileError(): void
    {
        $key = 'nonExistentFile.png';

        $awsClient = $this->createAwsMock();

        $s3Exception = new S3Exception(
            'Some other error message',
            new Command('getObject'),
            ['code' => 'InvalidRequest']
        );

        $awsClient->method('getObject')->with(['Bucket' => 'unit_test_bucket', 'Key' => $key])->willThrowException($s3Exception);

        $logger = self::createStub(LoggerInterface::class);

        $sut = new S3Storage($awsClient, 'unit_test_bucket', $logger);

        self::expectException(S3Exception::class);
        self::expectExceptionMessage('Some other error message');

        $sut->retrieve($key);
    }

    private function createMockStream(string $content): Stream
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        return new Stream($stream);
    }

    private function createAwsMock(): S3ClientInterface&MockObject
    {
        return $this->getMockBuilder(S3ClientInterface::class)
            ->addMethods([
                'deleteObject',
                'deleteObjects',
                'getObject',
                'listObjectVersions',
                'putObject',
                'getObjectTagging',
            ])->getMockForAbstractClass();
    }
}
