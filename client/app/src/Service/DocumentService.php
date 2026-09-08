<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service;

use OPG\Digideps\Frontend\Entity\DocumentInterface;
use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Model\MissingDocument;
use OPG\Digideps\Frontend\Model\RetrievedDocument;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\File\Storage\FileNotFoundException;
use OPG\Digideps\Frontend\Service\File\Storage\S3Storage;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class DocumentService
{
    public function __construct(
        private readonly S3Storage $s3Storage,
        private readonly RestClient $restClient,
        private readonly LoggerInterface $logger,
        private readonly Environment $twig
    ) {
    }

    /**
     * @return bool true if deleted from S3 and database
     */
    public function removeDocumentFromS3(Document $document): bool
    {
        $documentId = $document->getId();
        $storageRef = $document->getStorageReference();

        $endpointResult = '';
        $s3Result = [];

        try {
            if (is_numeric($documentId) && !empty($storageRef)) {
                //Ensure document is removed from s3 and database
                $s3Result = $this->deleteFromS3($document);
                //remove from db
                $endpointResult = $this->restClient->delete('document/' . $documentId);
            }
            if ($endpointResult) {
                $this->log('notice', "Document $documentId (s3 ref $storageRef) deleted successfully from db");
            } else {
                $this->log('error', "Document $documentId delete API failure");
            }

            return $s3Result && $endpointResult;
        } catch (\Throwable $e) {
            $message = "cannot delete $documentId, ref $storageRef. Error: " . $e->getMessage();
            $this->log('error', $message);

            // rethrow exception to be caught by controller
            throw $e;
        }
    }

    /**
     * @throws \Exception if the document doesn't exist (in addition to S3 network/access failures
     */
    private function deleteFromS3(DocumentInterface $document): array
    {
        $ref = $document->getStorageReference();
        if (!$ref) {
            $this->log('notice', 'empty file reference for document ' . $document->getId() . ', cannot delete');
            throw new \Exception('Document could not be removed. No Reference.');
        }

        $this->log('notice', "Deleting $ref from S3");
        $result = $this->s3Storage->removeFromS3($ref);

        $this->log('notice', "Deleting for $ref from S3: no exception thrown from deleteObject operation");

        return $result;
    }

    /**
     * Log message using the internal logger.
     *
     * @param $level
     * @param $message
     */
    private function log($level, $message)
    {
        //echo $message."\n"; //enable for debugging reasons. Tail the log with log-level=info otherwise

        $this->logger->log($level, $message, ['extra' => [
            'service' => 'documents-service',
        ]]);
    }

    /**
     * Waiting for PHP core to catch up with allowing return documentation for this Golang-like feature.
     * Returns two arrays utilising list() and array destructuring. Both values are accessible as variables
     * rather than accessing their array index.
     *
     * When calling this function use the format:
     *
     * [$retrievedDocuments, $missingDocuments] = retrieveDocumentsFromS3ByReportSubmission($reportSubmission);
     *
     * $retrievedDocuments - Array of RetrievedDocuments from S3
     * $missingDocuments - Array of MissingDocuments
     *
     * @return array
     */
    public function retrieveDocumentsFromS3ByReportSubmission(ReportSubmission $reportSubmission): array
    {
        $retrievedDocuments = [];
        $missingDocuments = [];

        foreach ($reportSubmission->getDocuments() as $document) {
            try {
                // AWS returns a object here - typecasting to string
                $contents = $this->s3Storage->retrieve($document->getStorageReference());

                $retrievedDocument = new RetrievedDocument();
                $retrievedDocument->setContent($contents);
                $retrievedDocument->setFileName($document->getFileName());
                $retrievedDocument->setReportSubmission($reportSubmission);

                $retrievedDocuments[] = $retrievedDocument;
            } catch (FileNotFoundException) {
                $missingDocument = new MissingDocument();
                $missingDocument->setFileName($document->getFileName() ?? 'unknown file name');
                $missingDocument->setReportSubmission($reportSubmission);

                $missingDocuments[] = $missingDocument;
            }
        }

        return [$retrievedDocuments, $missingDocuments];
    }

    /**
     * When calling this function use the format:.
     *
     * [$documents, $missing] = retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions);
     *
     * See retrieveDocumentsFromS3ByReportSubmission() docblock for background.
     *
     * @param array<ReportSubmission> $reportSubmissions
     */
    public function retrieveDocumentsFromS3ByReportSubmissions(array $reportSubmissions): array
    {
        $allDocuments = [];
        $allMissing = [];

        foreach ($reportSubmissions as $reportSubmission) {
            [$documents, $missing] = $this->retrieveDocumentsFromS3ByReportSubmission($reportSubmission);

            if (!empty($missing)) {
                $allMissing = array_merge($allMissing, $missing);
            }

            $allDocuments = array_merge($allDocuments, $documents);
        }

        return [$allDocuments, $allMissing];
    }

    /**
     * @param array<MissingDocument> $missingDocuments
     */
    public function createMissingDocumentsFlashMessage(array $missingDocuments): string
    {
        return $this->twig->render(
            '@App/FlashMessages/missing-documents.html.twig',
            ['missingDocuments' => $missingDocuments]
        );
    }
}
