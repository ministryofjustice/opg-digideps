<?php

namespace AppBundle\Service\File\Storage;

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

        $awsClient = new \Aws\S3\S3Client($options);

        $this->object = new S3Storage($awsClient, 'unit_test_bucket');

        // check fake S3 connection. To test why failing on the infrastructure
        if (!@fsockopen('fakes3', '4569')) {
            echo "Can't connect to S3 ({$options['endpoint']})\n";
            $this->markTestSkipped('fakes3 not responding');
        }
    }

    public function testUploadDownloadDeleteTextContent()
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(1);
        $fileContent = 'FILE-CONTENT-' . microtime(1);

        // store
        $ret = $this->object->store($key, $fileContent);
        $this->assertEquals(200, $ret->toArray()['@metadata']['statusCode']);

        // retrieve
        $this->assertEquals($fileContent, $this->object->retrieve($key));

        // delete
        $ret = $this->object->delete($key);
        $this->assertEquals(204, $ret->toArray()['@metadata']['statusCode']);

        // try retrieve after deletion (Exception expected)
        $this->setExpectedException(FileNotFoundException::class);
        $this->object->retrieve($key);
    }

    public function testUploadBinaryContent()
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete' . microtime(1) . '.png';
        $fileContent = file_get_contents(__DIR__ . '/cat.jpg');

        $this->object->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->object->retrieve($key));
    }
}
