<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Model\MissingDocument;
use OPG\Digideps\Frontend\Model\RetrievedDocument;
use OPG\Digideps\Frontend\Service\DocumentDownloader;
use OPG\Digideps\Frontend\Service\DocumentService;
use OPG\Digideps\Frontend\Service\File\DocumentsZipFileCreator;
use OPG\Digideps\Frontend\Service\ReportSubmissionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class DocumentDownloaderTest extends TestCase
{
    private MockObject&DocumentService $documentService;
    private MockObject&ReportSubmissionService $reportSubmissionService;
    private MockObject&DocumentsZipFileCreator $zipFileCreator;

    public function setUp(): void
    {
        $this->documentService = self::createMock(DocumentService::class);
        $this->reportSubmissionService = self::createMock(ReportSubmissionService::class);
        $this->zipFileCreator = self::createMock(DocumentsZipFileCreator::class);
    }

    public function testGenerateDownloadResponse(): void
    {
        $sut = new DocumentDownloader($this->documentService, $this->reportSubmissionService, $this->zipFileCreator);

        $zipFile = '/tmp/test-file.zip';
        file_put_contents($zipFile, 'some content');

        $response = $sut->generateDownloadResponse($zipFile);

        unset($zipFile);

        self::assertEquals('attachment; filename="test-file.zip";', $response->headers->get('Content-Disposition'));
    }

    public function testRetrieveDocumentsFromS3ByReportSubmissionIds(): void
    {
        $request = new Request();
        $ids = [1, 2];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission2 = new ReportSubmission();
        $reportSubmissions = [$reportSubmission1, $reportSubmission2];

        $this->reportSubmissionService->expects(self::once())
            ->method('getReportSubmissionsByIds')
            ->with($ids)
            ->willReturn($reportSubmissions);

        $this->reportSubmissionService->expects(self::exactly(2))
            ->method('assertReportSubmissionIsDownloadable');

        $document1 = new RetrievedDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setContent('content-1');
        $document1->setFileName('filename-1');
        $document2 = new RetrievedDocument();
        $document2->setReportSubmission($reportSubmission2);
        $document2->setContent('content-2');
        $document2->setFileName('filename-2');

        $expectedRetrievedDocuments = [$document1, $document2];

        $this->documentService->expects(self::once())
            ->method('retrieveDocumentsFromS3ByReportSubmissions')
            ->with($reportSubmissions)
            ->willReturn([$expectedRetrievedDocuments, []]);

        $sut = new DocumentDownloader($this->documentService, $this->reportSubmissionService, $this->zipFileCreator);
        [$retrievedDocuments, $missingDocuments] = $sut->retrieveDocumentsFromS3ByReportSubmissionIds($request, $ids);

        self::assertEquals($expectedRetrievedDocuments, $retrievedDocuments);
        self::assertEmpty($missingDocuments);
    }

    public function testProcessDownloadMissingDocument(): void
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $ids = [1, 2];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission2 = $this->generateReportSubmission('CaseNumber2');

        $reportSubmissions = [$reportSubmission1, $reportSubmission2];

        $this->reportSubmissionService->expects(self::once())
            ->method('getReportSubmissionsByIds')
            ->with($ids)
            ->willReturn($reportSubmissions);

        $this->reportSubmissionService->expects(self::exactly(2))
            ->method('assertReportSubmissionIsDownloadable');

        $document1 = new RetrievedDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setContent('content-1');
        $document1->setFileName('filename-1');
        $document2 = new MissingDocument();
        $document2->setReportSubmission($reportSubmission2);
        $document2->setFileName('filename-2');

        $expectedRetrievedDocuments = [$document1];
        $expectedMissingDocument = [$document2];

        $this->documentService->expects(self::once())
            ->method('retrieveDocumentsFromS3ByReportSubmissions')
            ->with($reportSubmissions)
            ->willReturn([$expectedRetrievedDocuments, $expectedMissingDocument]);

        $sut = new DocumentDownloader($this->documentService, $this->reportSubmissionService, $this->zipFileCreator);
        [$retrievedDocuments, $missingDocument] = $sut->retrieveDocumentsFromS3ByReportSubmissionIds($request, $ids);

        self::assertEquals($expectedRetrievedDocuments, $retrievedDocuments);
        self::assertEquals($missingDocument, $missingDocument);
    }

    private function generateReportSubmission(string $caseNumber): ReportSubmission
    {
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $report = new Report();
        $report->setClient($client);

        $reportSubmission = new ReportSubmission();
        $reportSubmission->setReport($report);

        return $reportSubmission;
    }

    public function testSetMissingDocsFlashMessage(): void
    {
        $this->documentService->expects(self::once())
            ->method('createMissingDocumentsFlashMessage')
            ->willReturn('flash message');

        $sut = new DocumentDownloader($this->documentService, $this->reportSubmissionService, $this->zipFileCreator);

        $reportSubmission1 = $this->generateReportSubmission('CaseNumber1');
        $reportSubmission2 = $this->generateReportSubmission('CaseNumber2');

        $document1 = new MissingDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setFileName('filename-2');

        $document2 = new MissingDocument();
        $document2->setReportSubmission($reportSubmission2);
        $document2->setFileName('filename-2');

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $sut->setMissingDocsFlashMessage($request, [$document1, $document2]);
        $actualFlash = $session->getFlashBag()->get('error')[0];
        self::assertEquals('flash message', $actualFlash);
    }

    public function testZipDownloadedDocuments(): void
    {
        $retrievedDocs = [new RetrievedDocument()];
        $zippedFiles = [new \ZipArchive()];

        $this->zipFileCreator->expects(self::once())
            ->method('createZipFilesFromRetrievedDocuments')
            ->with($retrievedDocs)
            ->willReturn($zippedFiles);

        $this->zipFileCreator->expects(self::once())
            ->method('createMultiZipFile')
            ->with($zippedFiles)
            ->willReturn('some-file.zip');

        $sut = new DocumentDownloader($this->documentService, $this->reportSubmissionService, $this->zipFileCreator);

        $sut->zipDownloadedDocuments($retrievedDocs);
    }
}
