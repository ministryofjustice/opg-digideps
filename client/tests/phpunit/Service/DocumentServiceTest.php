<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\MissingDocument;
use AppBundle\Model\RetrievedDocument;
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

    /**
     * @var ObjectProphecy|Document
     */
    private $doc1;

    /**
     * @var ObjectProphecy|Document
     */
    private $doc2;

    /**
     * @var ObjectProphecy|Document
     */
    private $doc3;

    /**
     * @var ObjectProphecy|Document
     */
    private $doc4;


    public function setUp(): void
    {
        $this->s3Storage = m::mock(S3Storage::class);
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();

        $this->object = new DocumentService($this->s3Storage, $this->restClient, $this->logger);

        $this->doc1 = self::prophesize(Document::class);
        $this->doc1->getStorageReference()->willReturn('ref-1');
        $this->doc1->getId()->willReturn(1);
        $this->doc1->getFileName()->willReturn('file-name1.pdf');

        $this->doc2 = self::prophesize(Document::class);
        $this->doc2->getStorageReference()->willReturn('ref-2');
        $this->doc2->getId()->willReturn(2);
        $this->doc2->getFileName()->willReturn('file-name2.pdf');

        $this->doc3 = self::prophesize(Document::class);
        $this->doc3->getStorageReference()->willReturn('ref-3');
        $this->doc3->getId()->willReturn(3);
        $this->doc3->getFileName()->willReturn('file-name3.pdf');

        $this->doc4 = self::prophesize(Document::class);
        $this->doc4->getStorageReference()->willReturn('ref-4');
        $this->doc4->getId()->willReturn(4);
        $this->doc4->getFileName()->willReturn('file-name4.pdf');
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

        $this->expectException('Exception');

        $this->object->removeDocumentFromS3($document);

    }

    /**
     * @group acss
     */
    public function testRetrieveDocumentsFromS3ByReportSubmission()
    {
        /** @var S3Storage|ObjectProphecy $storage */
        $storage = self::prophesize(S3Storage::class);
        $storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $storage->retrieve('ref-2')->shouldBeCalled()->willReturn('doc2 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());
        [$documents, $missing] = $sut->retrieveDocumentsFromS3ByReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission->reveal());

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);
        self::assertEmpty($missing);
    }

    /**
     * @group acss
     */
    public function testMissingDocumentsFileNamesAreReturnedIfNotRetrievable()
    {
        /** @var S3Storage|ObjectProphecy $storage */
        $storage = self::prophesize(S3Storage::class);
        $storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $storage->retrieve('ref-2')->shouldBeCalled()
            ->willThrow(new FileNotFoundException("Cannot find file with reference ref-2"));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());
        [$documents, $missing] = $sut->retrieveDocumentsFromS3ByReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc = new RetrievedDocument();
        $expectedRetrievedDoc->setFileName('file-name1.pdf');
        $expectedRetrievedDoc->setContent('doc1 contents');
        $expectedRetrievedDoc->setReportSubmission($reportSubmission->reveal());

        $expectedMissingDoc = new MissingDocument();
        $expectedMissingDoc->setFileName('file-name2.pdf');
        $expectedMissingDoc->setReportSubmission($reportSubmission->reveal());

        self::assertEquals([$expectedRetrievedDoc], $documents);
        self::assertEquals([$expectedMissingDoc], $missing);
    }

    /**
     * @group acss
     */
    public function testRetrieveDocumentsFromS3ByReportSubmissions()
    {
        /** @var S3Storage|ObjectProphecy $storage */
        $storage = self::prophesize(S3Storage::class);
        $storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $storage->retrieve('ref-2')->shouldBeCalled()->willReturn('doc2 contents');
        $storage->retrieve('ref-3')->shouldBeCalled()->willReturn('doc3 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc3->reveal()]));

        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());

        [$documents, $missing] = $sut->retrieveDocumentsFromS3ByReportSubmissions(
            [$reportSubmission->reveal(), $reportSubmission2->reveal()]
        );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name2.pdf');
        $expectedRetrievedDoc2->setContent('doc2 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc3 = new RetrievedDocument();
        $expectedRetrievedDoc3->setFileName('file-name3.pdf');
        $expectedRetrievedDoc3->setContent('doc3 contents');
        $expectedRetrievedDoc3->setReportSubmission($reportSubmission2->reveal());

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2, $expectedRetrievedDoc3], $documents);
        self::assertEmpty($missing);
    }

    /**
     * @group acss
     */
    public function testRetrieveDocumentsFromS3ByReportSubmissionsMissingDocs()
    {
        /** @var S3Storage|ObjectProphecy $storage */
        $storage = self::prophesize(S3Storage::class);
        $storage->retrieve('ref-1')->shouldBeCalled()->willReturn('doc1 contents');
        $storage->retrieve('ref-2')->shouldBeCalled()
            ->willThrow(new FileNotFoundException("Cannot find file with reference ref-2"));
        $storage->retrieve('ref-3')->shouldBeCalled()
            ->willThrow(new FileNotFoundException("Cannot find file with reference ref-3"));
        $storage->retrieve('ref-4')->shouldBeCalled()->willReturn('doc4 contents');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc1->reveal(), $this->doc2->reveal()]));

        /** @var ObjectProphecy|ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getDocuments()
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$this->doc3->reveal(), $this->doc4->reveal()]));

        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());

        [$documents, $missing] = $sut->retrieveDocumentsFromS3ByReportSubmissions(
            [$reportSubmission->reveal(), $reportSubmission2->reveal()]
        );

        $expectedRetrievedDoc1 = new RetrievedDocument();
        $expectedRetrievedDoc1->setFileName('file-name1.pdf');
        $expectedRetrievedDoc1->setContent('doc1 contents');
        $expectedRetrievedDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedRetrievedDoc2 = new RetrievedDocument();
        $expectedRetrievedDoc2->setFileName('file-name4.pdf');
        $expectedRetrievedDoc2->setContent('doc4 contents');
        $expectedRetrievedDoc2->setReportSubmission($reportSubmission2->reveal());

        self::assertEquals([$expectedRetrievedDoc1, $expectedRetrievedDoc2], $documents);

        $expectedMissingDoc1 = new MissingDocument();
        $expectedMissingDoc1->setFileName('file-name2.pdf');
        $expectedMissingDoc1->setReportSubmission($reportSubmission->reveal());

        $expectedMissingDoc2 = new MissingDocument();
        $expectedMissingDoc2->setFileName('file-name3.pdf');
        $expectedMissingDoc2->setReportSubmission($reportSubmission2->reveal());

        self::assertEquals([$expectedMissingDoc1, $expectedMissingDoc2], $missing);
    }

    /**
     * @group acss
     */
    public function testCreateMissingDocumentsFlashMessage()
    {
        /** @var ObjectProphecy|ReportSubmission $reportSubmission */
        $reportSubmission1 = self::prophesize(ReportSubmission::class);
        $reportSubmission1->getCaseNumber()->shouldBeCalled()->willReturn('CaseNumber1');

        /** @var ObjectProphecy|ReportSubmission $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getCaseNumber()->shouldBeCalled()->willReturn('CaseNumber2');

        $missingDoc1 = new MissingDocument();
        $missingDoc1->setFileName('file-name1.pdf');
        $missingDoc1->setReportSubmission($reportSubmission1->reveal());

        $missingDoc2 = new MissingDocument();
        $missingDoc2->setFileName('file-name2.pdf');
        $missingDoc2->setReportSubmission($reportSubmission2->reveal());

        $missingDoc3 = new MissingDocument();
        $missingDoc3->setFileName('file-name3.pdf');
        $missingDoc3->setReportSubmission($reportSubmission1->reveal());

        $missingDocuments = [$missingDoc1, $missingDoc2, $missingDoc3];

        $expectedFlash = <<<FLASH
The following documents could not be downloaded:
<ul><li>CaseNumber1 - file-name1.pdf</li><li>CaseNumber2 - file-name2.pdf</li><li>CaseNumber1 - file-name3.pdf</li></ul>
FLASH;

        $storage = self::prophesize(S3Storage::class);
        $logger = self::prophesize(LoggerInterface::class);
        $restClient = self::prophesize(RestClient::class);

        $sut = new DocumentService($storage->reveal(), $restClient->reveal(), $logger->reveal());
        $actualFlash = $sut->createMissingDocumentsFlashMessage($missingDocuments);

        self::assertEquals($expectedFlash, $actualFlash);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
