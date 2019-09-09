<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\FileNotFoundException;
use AppBundle\Service\File\Storage\S3Storage;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Exception;
use Mockery as m;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class DocumentServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var DocumentService
     */
    protected $object;

    private $s3Storage;

    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->s3Storage = m::mock(S3Storage::class);
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();

        $this->object = new DocumentService($this->s3Storage, $this->restClient, $this->logger);
    }


    public static function cleanUpDataProvider()
    {
        return [
            [0], // s3 failures NOT ignored -> hard delete gets called
            [1], // s3 failures ignored -> hard delete gets called
        ];
    }

    public function testRemoveDocumentFromS3()
    {
        $docId = 1;
        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage
            ->shouldReceive('removeFromS3')->once()->with('r1')->andReturn([]);

        $this->restClient->shouldReceive('delete')
            ->once()
            ->with('document/' . $docId)
            ->andReturn(true);

        $this->object->removeDocumentFromS3($document);

    }

    public function testRemoveDocumentWithS3Failure()
    {
        $docId = 1;

        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage
            ->shouldReceive('removeFromS3')->once()->with('r1')->andThrow(Exception::class);

        $this->restClient->shouldReceive('apiCall')->never()->with('DELETE', 'document/1', null, 'array', [], false);

        $this->setExpectedException('Exception');

        $this->object->removeDocumentFromS3($document);

    }

    /**
     * @group acs
     */
    public function testRetrieveDocumentsFromS3ForReportSubmission()
    {
        $doc1 = self::prophesize(Document::class);
        $doc1->getStorageReference()->willReturn('ref-1');
        $doc1->getId()->willReturn(1);
        $doc1->getFileName()->willReturn('file-name1.pdf');

        $doc2 = self::prophesize(Document::class);
        $doc2->getStorageReference()->willReturn('ref-2');
        $doc2->getId()->willReturn(2);
        $doc2->getFileName()->willReturn('file-name2.pdf');

        /** @var S3Storage|ObjectProphecy $storage */
        $storage = self::prophesize(S3Storage::class);
        $storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $storage->retrieve('ref-2')->shouldBeCalled()->willReturn('doc2 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$doc1->reveal(), $doc2->reveal()]));

        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());
        [$documents, $missing] = $sut->retrieveDocumentsFromS3ForReportSubmission($reportSubmission->reveal());

        self::assertEquals(['file-name1.pdf' => 'doc1 contents', 'file-name2.pdf' => 'doc2 contents'], $documents);
        self::assertEmpty($missing);
    }

    /**
     * @group acs
     */
    public function testMissingDocumentsFileNamesAreReturnedIfNotRetrievable()
    {
        $doc1 = self::prophesize(Document::class);
        $doc1->getStorageReference()->willReturn('ref-1');
        $doc1->getId()->willReturn(1);
        $doc1->getFileName()->willReturn('file-name1.pdf');

        $doc2 = self::prophesize(Document::class);
        $doc2->getStorageReference()->willReturn('ref-2');
        $doc2->getId()->willReturn(2);
        $doc2->getFileName()->willReturn('file-name2.pdf');

        /** @var S3Storage|ObjectProphecy $storage */
        $storage = self::prophesize(S3Storage::class);
        $storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $storage->retrieve('ref-2')->shouldBeCalled()
            ->willThrow(new FileNotFoundException("Cannot find file with reference ref-2"));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$doc1->reveal(), $doc2->reveal()]));

        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());
        [$documents, $missing] = $sut->retrieveDocumentsFromS3ForReportSubmission($reportSubmission->reveal());

        self::assertEquals(['file-name1.pdf' => 'doc1 contents'], $documents);
        self::assertEquals(['file-name2.pdf'], $missing);
    }

    public function tearDown()
    {
        m::close();
    }
}
