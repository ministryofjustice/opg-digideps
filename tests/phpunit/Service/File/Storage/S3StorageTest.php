<?php

namespace AppBundle\Service\File\Storage;

use Aws\Command;
use Aws\Exception\AwsException;
use Mockery as m;
use Psr\Log\LoggerInterface;

class S3StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var S3Storage
     */
    private $object;

    public function setUp()
    {
        // connect to localstack
        // see docker-composer.yml for params

        $this->fileContent = 'FILE-CONTENT-' . microtime(1);

        $options =[
            'version'     => 'latest',
            'region'      => 'eu-west-1',
            'endpoint'    => 'http://localstack:4572',
            'validate'    => false,
            'credentials' => [
                'key'    => 'YOUR_ACCESS_KEY_ID',
                'secret' => 'YOUR_SECRET_ACCESS_KEY',
            ],
        ];

        // check localstack connection. To test why failing on the infrastructure
        if (!@fsockopen('localstack', '4572')) {
//            $this->markTestSkipped('localstack not responding');
//            echo "Can't connect to S3 ({$options['endpoint']})\n";
        }
    }

    private function generateAwsResult($statusCode, $data = [], $body  ='')
    {
        $args = array_merge(['@metadata' => ['statusCode' => $statusCode]], $data);
        if (!empty($body)) {
            $args['Body'] = $body;
        }
        return new \Aws\Result($args);
    }

    public function testUploadDownloadDeleteTextContent()
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(1);

        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);

        $awsClient->shouldReceive('putObject')
            ->with(m::type('array'))
            ->once()
            ->andReturn($this->generateAwsResult(200));

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
            ->andReturn($this->generateAwsResult(200, [], $this->fileContent));

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
        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);
        $awsClient->shouldReceive('getObject')->with(
            m::type('array')
        )->andThrow(FileNotFoundException::class);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // try retrieve after deletion (Exception expected)
        $this->setExpectedException(FileNotFoundException::class);
        $this->object->retrieve($key);
    }

    public function testUploadBinaryContent()
    {
        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);

        $awsClient->shouldReceive('putObject')->andReturn($this->generateAwsResult(200));
        $awsClient->shouldReceive('getObject')->with(
            m::type('array')
        )->andReturn($this->generateAwsResult(200, [], file_get_contents(__DIR__ . '/cat.jpg')));

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $this->object->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->object->retrieve($key));
    }

    public function testRemoveFromS3NoErrors()
    {
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';

        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key
            ])->andReturn($this->generateAwsResult(
                200,
                [
                    'Versions' => [
                            0 => [
                                'Key' => $key,
                                'VersionId' => 'testVersionId_1'
                            ],
                            1 => [
                                'Key' => $key,
                                'VersionId' => 'testVersionId_2'
                            ]
                    ]
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
                    ]
                ]
            ])->andReturn($this->generateAwsResult(200));

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $result = $this->object->removeFromS3($key);
            $this->assertEquals(
            [
                ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                ['Key' => $key, 'VersionId' => 'testVersionId_2']
            ],
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithErrors()
    {
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';

        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key
            ])->andReturn($this->generateAwsResult(
            200,
            [
                'Versions' => [
                    0 => [
                        'Key' => $key,
                        'VersionId' => 'testVersionId_1'
                    ],
                    1 => [
                        'Key' => $key,
                        'VersionId' => 'testVersionId_2'
                    ]
                ]
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
                    ]
                ]
            ])->andReturn($this->generateAwsResult(
                200,
                [
                    'Errors' => [
                        ['Key' => $key, 'VersionId' => 'testVersionId_1', 'Code' => 'AccessDenied', 'Message' => 'Access Denied.'],
                        ['Key' => $key, 'VersionId' => 'testVersionId_2', 'Code' => 'AccessDenied', 'Message' => 'Access Denied.']
                    ]
                ]
            )
        );

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->setExpectedException('RuntimeException', 'Could not remove file');

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            [
                ['Key' => $key, 'VersionId' => 'testVersionId_1'],
                ['Key' => $key, 'VersionId' => 'testVersionId_2']
            ],
            $result['objectsToDelete']
        );
    }

    public function testRemoveFromS3WithKeyNotFound()
    {
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';

        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key
            ])->andReturn($this->generateAwsResult(404)
        );

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

    public function testRemoveFromS3WhenS3NotWorking()
    {
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';

        $awsClient = m::mock(\Aws\S3\S3ClientInterface::class);

        $awsClient->shouldReceive('listObjectVersions')->with(
            [
                'Bucket' => 'unit_test_bucket',
                'Prefix' => $key
            ])->andReturn(
                new AwsException('AWS is down', new Command('listObjectVersions'), ['code' => 500])
        );

        $awsClient->shouldNotReceive('deleteObjects')->never();

        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('log')->withAnyArgs();

        $this->object = new S3Storage($awsClient, 'unit_test_bucket', $mockLogger);

        $this->setExpectedException('RuntimeException', 'Could not remove file');

        $result = $this->object->removeFromS3($key);
        $this->assertEquals(
            '',
            $result['objectsToDelete']
        );
    }
}
