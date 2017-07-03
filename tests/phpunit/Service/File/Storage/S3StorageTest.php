<?php

namespace AppBundle\Service\File\Storage;

use AppBundle\Service\Client\RestClient;
use GuzzleHttp\Client as GuzzleHttpClient;
use Mockery as m;
use Symfony\Bridge\Monolog\Logger;

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
        $awsClient = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => 'eu-west-1',
            'endpoint' =>  'http://fakes3:4569',
            'validate' => false,
            'credentials' => [
                'key'    => 'YOUR_ACCESS_KEY_ID',
                'secret' => 'YOUR_SECRET_ACCESS_KEY',
            ],
        ]);

        $this->object = new S3Storage($awsClient, 'unitTestBucket');
    }

    public function testUploadDownloadDeleteTextContent()
    {
        // create timestamped file and key to undo effects of potential previous executions
        $key = 'storagetest-upload-download-delete'.microtime(1);
        $fileContent = 'FILE-CONTENT-'.microtime(1);

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
        $key = 'storagetest-upload-download-delete'.microtime(1).'.png';
        $fileContent = file_get_contents(__DIR__.'/cat.jpg');

        $this->object->store($key, $fileContent);
        $this->assertEquals($fileContent, $this->object->retrieve($key));
    }
}
