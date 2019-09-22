<?php declare(strict_types=1);


namespace AppBundle\Service;


use AppBundle\Service\File\DocumentsZipFileCreator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class DocumentDownloader
{
    const ACTION_DOWNLOAD = 'download';
    const MSG_NOT_DOWNLOADABLE = 'This report is not downloadable';

    /**
     * @var DocumentService
     */
    private $documentService;

    /**
     * @var ReportSubmissionService
     */
    private $reportSubmissionService;

    /**
     * @var DocumentsZipFileCreator
     */
    private $zipFileCreator;

    public function __construct(
        DocumentService $documentService,
        ReportSubmissionService $reportSubmissionService,
        DocumentsZipFileCreator $zipFileCreator
    )
    {
        $this->documentService = $documentService;
        $this->reportSubmissionService = $reportSubmissionService;
        $this->zipFileCreator = $zipFileCreator;
    }

    /**
     * Download multiple documents based on the supplied ids
     *
     * @param Request $request
     * @param []string $reportSubmissionIds, an Array of ReportSubmission ids to be downloaded
     *
     * @return array
     */
    public function retrieveDocumentsFromS3ByReportSubmissionIds(Request $request, array $reportSubmissionIds)
    {
        try {
            $reportSubmissions = $this->reportSubmissionService->getReportSubmissionsByIds($reportSubmissionIds);

            foreach ($reportSubmissions as $reportSubmission) {
                $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission);
            }

            return [$retrievedDocuments, $missingDocuments] = $this->documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions);
        } catch (\Throwable $e) {
            $this->zipFileCreator->cleanUp();
            throw $e;
        }
    }

    public function setMissingDocsFlashMessage(Request $request, array $missingDocuments)
    {
        $flashMessage = $this->documentService->createMissingDocumentsFlashMessage($missingDocuments);
        $this->getFlashBag($request)->add('error', $flashMessage);
    }

    public function zipDownloadedDocuments(array $retrievedDocuments)
    {
        $zipFiles = $this->zipFileCreator->createZipFilesFromRetrievedDocuments($retrievedDocuments);
        $fileName = $this->zipFileCreator->createMultiZipFile($zipFiles);

        return $fileName;
    }

    public function generateDownloadResponse(string $fileName)
    {
        $response = new Response();
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires', '0');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($fileName) . '";');
        $response->sendHeaders();
        $response->setContent(readfile($fileName));

        $this->zipFileCreator->cleanUp();

        return $response;
    }

    /**
     * @param Request $request
     * @return FlashBag
     */
    public function getFlashBag(Request $request)
    {
        return $request->getSession()->getFlashBag();
    }
}
