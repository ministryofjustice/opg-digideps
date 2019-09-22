<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\MissingDocument;
use AppBundle\Model\RetrievedDocument;
use AppBundle\Service\File\DocumentsZipFileCreator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use ZipArchive;

class DocumentDownloaderTest extends TestCase
{
    /**
     * @var ObjectProphecy|DocumentService
     */
    private $documentService;

    /**
     * @var ObjectProphecy|ReportSubmissionService
     */
    private $reportSubmissionService;

    /**
     * @var ObjectProphecy|DocumentsZipFileCreator
     */
    private $zipFileCreator;

    public function setUp():void
    {
        $this->documentService = self::prophesize(DocumentService::class);
        $this->reportSubmissionService = self::prophesize(ReportSubmissionService::class);
        $this->zipFileCreator = self::prophesize(DocumentsZipFileCreator::class);
    }

    public function testGenerateDownloadResponse()
    {
        $sut = new DocumentDownloader($this->documentService->reveal(), $this->reportSubmissionService->reveal(), $this->zipFileCreator->reveal());

        $zipFile = "/tmp/test-file.zip";
        file_put_contents($zipFile, 'some content');

        $response = $sut->generateDownloadResponse($zipFile);

        unset($zipFile);

        self::assertEquals('attachment; filename="test-file.zip";', $response->headers->get('Content-Disposition'));
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissionIds()
    {
        $request = new Request();
        $ids = [1, 2];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission2 = new ReportSubmission();
        $reportSubmissions = [$reportSubmission1, $reportSubmission2];

        $this->reportSubmissionService->getReportSubmissionsByIds($ids)->willReturn($reportSubmissions);
        $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission1)->willReturn(null);
        $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission2)->willReturn(null);

        $document1 = new RetrievedDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setContent('content-1');
        $document1->setFileName('filename-1');
        $document2 = new RetrievedDocument();
        $document2->setReportSubmission($reportSubmission2);
        $document2->setContent('content-2');
        $document2->setFileName('filename-2');

        $expectedRetrievedDocuments = [$document1, $document2];

        $this->documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions)->willReturn([$expectedRetrievedDocuments, []]);

        $sut = new DocumentDownloader($this->documentService->reveal(), $this->reportSubmissionService->reveal(), $this->zipFileCreator->reveal());
        [$retrievedDocuments, $missingDocuments] = $sut->retrieveDocumentsFromS3ByReportSubmissionIds($request, $ids);

        self::assertEquals($expectedRetrievedDocuments, $retrievedDocuments);
        self::assertEmpty($missingDocuments);
    }

    public function testProcessDownloadMissingDocument()
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $ids = [1, 2];

        $reportSubmission1 = new ReportSubmission();
        /** @var ReportSubmission|ObjectProphecy $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getCaseNumber()->willReturn('CaseNumber2');

        $reportSubmissions = [$reportSubmission1, $reportSubmission2->reveal()];

        $this->reportSubmissionService->getReportSubmissionsByIds($ids)->willReturn($reportSubmissions);
        $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission1)->willReturn(null);
        $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission2)->willReturn(null);

        $document1 = new RetrievedDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setContent('content-1');
        $document1->setFileName('filename-1');
        $document2 = new MissingDocument();
        $document2->setReportSubmission($reportSubmission2->reveal());
        $document2->setFileName('filename-2');

        $expectedRetrievedDocuments = [$document1];
        $expectedMissingDocument = [$document2];

        $this->documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions)
            ->willReturn([$expectedRetrievedDocuments, $expectedMissingDocument]);

        $sut = new DocumentDownloader($this->documentService->reveal(), $this->reportSubmissionService->reveal(), $this->zipFileCreator->reveal());
        [$retrievedDocuments, $missingDocument] = $sut->retrieveDocumentsFromS3ByReportSubmissionIds($request, $ids);

        self::assertEquals($expectedRetrievedDocuments, $retrievedDocuments);
        self::assertEquals($missingDocument, $missingDocument);
    }

    public function testSetMissingDocsFlashMessage()
    {
        $this->documentService->createMissingDocumentsFlashMessage(Argument::type('Array'))->willReturn('flash message');

        $sut = new DocumentDownloader($this->documentService->reveal(), $this->reportSubmissionService->reveal(), $this->zipFileCreator->reveal());

        /** @var ReportSubmission|ObjectProphecy $reportSubmission2 */
        $reportSubmission1 = self::prophesize(ReportSubmission::class);
        $reportSubmission1->getCaseNumber()->willReturn('CaseNumber1');

        /** @var ReportSubmission|ObjectProphecy $reportSubmission2 */
        $reportSubmission2 = self::prophesize(ReportSubmission::class);
        $reportSubmission2->getCaseNumber()->willReturn('CaseNumber2');

        $document1 = new MissingDocument();
        $document1->setReportSubmission($reportSubmission1->reveal());
        $document1->setFileName('filename-2');

        $document2 = new MissingDocument();
        $document2->setReportSubmission($reportSubmission2->reveal());
        $document2->setFileName('filename-2');

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $sut->setMissingDocsFlashMessage($request, [$document1, $document2]);
        $actualFlash = $session->getFlashBag()->get('error')[0];
        self::assertEquals('flash message', $actualFlash);
    }

    public function testZipDownloadedDocuments()
    {
        $retrievedDocs = [new RetrievedDocument()];
        $this->zipFileCreator->createZipFilesFromRetrievedDocuments($retrievedDocs)->shouldBeCalled()->willReturn([new ZipArchive()]);
        $this->zipFileCreator->createMultiZipFile(Argument::type('Array'))->shouldBeCalled()->willReturn('some-file.zip');
        $sut = new DocumentDownloader($this->documentService->reveal(), $this->reportSubmissionService->reveal(), $this->zipFileCreator->reveal());

        $sut->zipDownloadedDocuments($retrievedDocs);
    }

    protected function generateTestZipFiles(ZipArchive $zip, array $zipFileContent)
    {
        $zipFiles = [];

        foreach($zipFileContent as $fileName => $content) {
            $zipFile = "/tmp/${fileName}";
            file_put_contents($zipFile, $content);

            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);
            $zip->addFile($zipFile, $zipFile);

            $zipFiles[] = $zipFile;

            $zip->close();
            unset($zip);
        }

        return $zipFiles;
    }
}
