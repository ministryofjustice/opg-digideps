<?php declare(strict_types=1);


namespace AppBundle\Service;


use AppBundle\Service\File\DocumentsZipFileCreator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     */
    public function processDownload(Request $request, array $reportSubmissionIds)
    {
        try {
            $reportSubmissions = $this->reportSubmissionService->getReportSubmissionsByIds($reportSubmissionIds);

            foreach ($reportSubmissions as $reportSubmission) {
                $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission);
            }

            [$retrievedDocuments, $missingDocuments] = $this->documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions);

            $zipFiles = $this->zipFileCreator->createZipFilesFromRetrievedDocuments($retrievedDocuments);
            $fileName = $this->zipFileCreator->createMultiZipFile($zipFiles);

            // send ZIP to user
            $response = self::generateDownloadResponse($fileName);

            $this->zipFileCreator->cleanUp();

            if (!empty($missingDocuments)) {
                $flashMessage = $this->documentService->createMissingDocumentsFlashMessage($missingDocuments);
                $request->getSession()->getFlashBag()->add('error', $flashMessage);
            }

            return $response;
        } catch (\Throwable $e) {
            $this->zipFileCreator->cleanUp();
            $request->getSession()->getFlashBag()->add('error', 'Cannot download documents. Details: ' . $e->getMessage());
        }
    }

    public static function generateDownloadResponse(string $fileName)
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
        return $response;
    }
}
