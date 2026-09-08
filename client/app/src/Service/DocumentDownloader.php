<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service;

use OPG\Digideps\Frontend\Model\MissingDocument;
use OPG\Digideps\Frontend\Model\RetrievedDocument;
use OPG\Digideps\Frontend\Service\File\DocumentsZipFileCreator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class DocumentDownloader
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly ReportSubmissionService $reportSubmissionService,
        private readonly DocumentsZipFileCreator $zipFileCreator
    ) {
    }

    /**
     * Download multiple documents based on the supplied ids
     *
     * @param array<int> $reportSubmissionIds, an array of ReportSubmission ids to be downloaded
     * @return array{'retrieved': array<RetrievedDocument>, 'missing': array<MissingDocument>}
     */
    public function retrieveDocumentsFromS3ByReportSubmissionIds(array $reportSubmissionIds): array
    {
        try {
            $reportSubmissions = $this->reportSubmissionService->getReportSubmissionsByIds($reportSubmissionIds);

            foreach ($reportSubmissions as $reportSubmission) {
                $this->reportSubmissionService->assertReportSubmissionIsDownloadable($reportSubmission);
            }

            return $this->documentService->retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions);
        } catch (\Throwable $e) {
            $this->zipFileCreator->cleanUp();
            throw $e;
        }
    }

    /**
     * @param array<MissingDocument> $missingDocuments
     */
    public function setMissingDocsFlashMessage(Request $request, array $missingDocuments): void
    {
        $flashMessage = $this->documentService->createMissingDocumentsFlashMessage($missingDocuments);
        $this->getFlashBag($request)->add('error', $flashMessage);
    }

    /**
     * @param array<RetrievedDocument> $retrievedDocuments
     */
    public function zipDownloadedDocuments(array $retrievedDocuments): string
    {
        $zipFiles = $this->zipFileCreator->createZipFilesFromRetrievedDocuments($retrievedDocuments);
        return $this->zipFileCreator->createMultiZipFile($zipFiles);
    }

    public function generateDownloadResponse(string $fileName): Response
    {
        $response = new Response();
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires', '0');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($fileName) . '";');
        $response->sendHeaders();
        $response->setContent(file_get_contents($fileName));

        $this->zipFileCreator->cleanUp();

        return $response;
    }

    public function getFlashBag(Request $request): FlashBag
    {
        return $request->getSession()->getFlashBag();
    }
}
