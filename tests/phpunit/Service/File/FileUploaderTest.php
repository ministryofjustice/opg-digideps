<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Mockery as m;

class FileUploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileUploader
     */
    private $object;

    public function setUp()
    {
        $this->storage = m::mock(StorageInterface::class);
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();

        $this->object = new FileUploader($this->storage, $this->restClient, $this->logger);
    }

    public function testuploadFile()
    {
        $fileName = 'dd_fileuploadertest.pdf';
        $fileContent = 'testcontent';

        $this->storage->shouldReceive('store')->once()->with(matchesPattern('/^dd_doc_1_\d+$/'), $fileContent);
        $this->restClient->shouldReceive('post')->once()->with('/report/1/document', anInstanceOf(Document::class), ['document']);

        $doc = $this->object->uploadFile(1, $fileContent, $fileName); /* @var $document Document */

        $this->assertStringMatchesFormat('dd_doc_1_%d', $doc->getStorageReference());
        $this->assertEquals($fileName, $doc->getFileName());

    }

    public function tearDown()
    {
        m::close();
    }
}
