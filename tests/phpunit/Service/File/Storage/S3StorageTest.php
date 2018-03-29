<?php

namespace AppBundle\Service\File\Storage;

use Mockery as m;

class S3StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var S3Storage
     */
    private $object;

    public function setUp()
    {
        // connect to fakes3 (https://github.com/jubos/fake-s3)
        // see docker-composer.yml for params

        $this->fileContent = 'FILE-CONTENT-' . microtime(1);

        $options =[
            'version'     => 'latest',
            'region'      => 'eu-west-1',
            'endpoint'    => 'http://fakes3:4569',
            'validate'    => false,
            'credentials' => [
                'key'    => 'YOUR_ACCESS_KEY_ID',
                'secret' => 'YOUR_SECRET_ACCESS_KEY',
            ],
        ];

        // check fake S3 connection. To test why failing on the infrastructure
        if (!@fsockopen('fakes3', '4569')) {
            echo "Can't connect to S3 ({$options['endpoint']})\n";
            $this->markTestSkipped('fakes3 not responding');
        }
    }

    private function generateAwsResult($statusCode, $body  ='')
    {
        $args = ['@metadata' => ['statusCode' => $statusCode]];
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
            ->andReturn($this->generateAwsResult(200, $this->fileContent));

        $this->object = new S3Storage($awsClient, 'unit_test_bucket');

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

        $this->object = new S3Storage($awsClient, 'unit_test_bucket');

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
        )->andReturn($this->generateAwsResult(200, file_get_contents(__DIR__ . '/cat.jpg')));

        $this->object = new S3Storage($awsClient, 'unit_test_bucket');

        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $this->object->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->object->retrieve($key));
    }
}
