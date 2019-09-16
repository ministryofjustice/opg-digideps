<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\MissingDocument;
use AppBundle\Model\RetrievedDocument;
use AppBundle\Service\File\DocumentsZipFileCreator;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use ZipArchive;

class DocumentDownloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group acss
     */
    public function testGenerateDownloadResponse()
    {
        $documentService = self::prophesize(DocumentService::class);
        $reportSubmissionService = self::prophesize(ReportSubmissionService::class);
        $zipFileCreator = self::prophesize(DocumentsZipFileCreator::class);

        $sut = new DocumentDownloader($documentService->reveal(), $reportSubmissionService->reveal(), $zipFileCreator->reveal());

        $zipFile = "/tmp/test-file.zip";

        $response = $sut::generateDownloadResponse($zipFile);

        self::assertEquals('attachment; filename="test-file.zip";', $response->headers->get('Content-Disposition'));
    }

    /**
     * @group acss
     */
    public function testProcessDownload()
    {
        $request = new Request();
        $ids = [1, 2];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission2 = new ReportSubmission();
        $reportSubmissions = [$reportSubmission1, $reportSubmission2];

        /** @var DocumentService|ObjectProphecy $documentService */
        $documentService = self::prophesize(DocumentService::class);
        /** @var ReportSubmissionService|ObjectProphecy $reportSubmissionService */
        $reportSubmissionService = self::prophesize(ReportSubmissionService::class);
        /** @var DocumentsZipFileCreator|ObjectProphecy $zipFileCreator */
        $zipFileCreator = self::prophesize(DocumentsZipFileCreator::class);

        $reportSubmissionService->getReportSubmissionsByIds($ids)->willReturn($reportSubmissions);
        $reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission1)->willReturn(null);
        $reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission2)->willReturn(null);

        $document1 = new RetrievedDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setContent('content-1');
        $document1->setFileName('filename-1');
        $document2 = new RetrievedDocument();
        $document2->setReportSubmission($reportSubmission2);
        $document2->setContent('content-2');
        $document2->setFileName('filename-2');

        $retrievedDocuments = [$document1, $document2];

        $documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions)->willReturn([$retrievedDocuments, []]);
        $zipFileCreator->createZipFilesFromRetrievedDocuments($retrievedDocuments)->willReturn(['/tmp/filename1.zip', '/tmp/filename2.zip']);
        $zipFileCreator->createMultiZipFile(['/tmp/filename1.zip', '/tmp/filename2.zip'])->willReturn('/tmp/zipped-parent-file.zip');
        file_put_contents('/tmp/zipped-parent-file.zip', 'some content');
        $zipFileCreator->cleanUp()->willReturn(null);

        $sut = new DocumentDownloader($documentService->reveal(), $reportSubmissionService->reveal(), $zipFileCreator->reveal());
        $response = $sut->processDownload($request, $ids);

        self::assertEquals('attachment; filename="zipped-parent-file.zip";', $response->headers->get('Content-Disposition'));
    }

    /**
     * @group acss
     */
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

        /** @var DocumentService|ObjectProphecy $documentService */
        $documentService = self::prophesize(DocumentService::class);
        /** @var ReportSubmissionService|ObjectProphecy $reportSubmissionService */
        $reportSubmissionService = self::prophesize(ReportSubmissionService::class);
        /** @var DocumentsZipFileCreator|ObjectProphecy $zipFileCreator */
        $zipFileCreator = self::prophesize(DocumentsZipFileCreator::class);

        $reportSubmissionService->getReportSubmissionsByIds($ids)->willReturn($reportSubmissions);
        $reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission1)->willReturn(null);
        $reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission2)->willReturn(null);

        $document1 = new RetrievedDocument();
        $document1->setReportSubmission($reportSubmission1);
        $document1->setContent('content-1');
        $document1->setFileName('filename-1');
        $document2 = new MissingDocument();
        $document2->setReportSubmission($reportSubmission2->reveal());
        $document2->setFileName('filename-2');

        $retrievedDocuments = [$document1];
        $missingDocument = [$document2];

        $documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions)->willReturn([$retrievedDocuments, $missingDocument]);
        $expectedFlash = <<<FLASH
The following documents could not be downloaded:
<ul><li>CaseNumber2 - filename2</li></ul>
FLASH;

        $documentService->createMissingDocumentsFlashMessage($missingDocument)->willReturn($expectedFlash);

        $zipFileCreator->createZipFilesFromRetrievedDocuments($retrievedDocuments)->willReturn(['/tmp/filename1.zip']);
        $zipFileCreator->createMultiZipFile(['/tmp/filename1.zip'])->willReturn('/tmp/zipped-parent-file.zip');
        file_put_contents('/tmp/zipped-parent-file.zip', 'some content');
        $zipFileCreator->cleanUp()->willReturn(null);

        $sut = new DocumentDownloader($documentService->reveal(), $reportSubmissionService->reveal(), $zipFileCreator->reveal());
        $response = $sut->processDownload($request, $ids);

        self::assertEquals('attachment; filename="zipped-parent-file.zip";', $response->headers->get('Content-Disposition'));
        self::assertEquals($expectedFlash, $request->getSession()->getFlashBag()->get('error')[0]);
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
