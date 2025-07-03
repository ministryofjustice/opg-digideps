<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DocumentInterface;
use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\Model\MissingDocument;
use App\Model\RetrievedDocument;
use App\Service\Client\RestClient;
use App\Service\File\Storage\ClientS3Storage;
use App\Service\File\Storage\FileNotFoundException;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class DocumentService
{
    /**
     * @var ClientS3Storage
     */
    private $s3Storage;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * DocumentService constructor.
     */
    public function __construct(ClientS3Storage $s3Storage, RestClient $restClient, LoggerInterface $logger, Environment $twig)
    {
        $this->s3Storage = $s3Storage;
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->twig = $twig;
    }

    /**
     * @return bool true if deleted from S3 and database
     */
    public function removeDocumentFromS3(Document $document)
    {
        $documentId = $document->getId();
        $storageRef = $document->getStorageReference();

        $endpointResult = '';
        $s3Result = [];

        try {
            if (is_numeric($documentId) && !empty($storageRef)) {
                // Ensure document is removed from s3 and database
                $s3Result = $this->deleteFromS3($document);
                // remove from db
                $endpointResult = $this->restClient->delete('document/'.$documentId);
            }
            if ($endpointResult) {
                $this->log('notice', "Document $documentId (s3 ref $storageRef) deleted successfully from db");
            } else {
                $this->log('error', "Document $documentId delete API failure");
            }

            return $s3Result && $endpointResult;
        } catch (\Throwable $e) {
            $message = "cannot delete $documentId, ref $storageRef. Error: ".$e->getMessage();
            $this->log('error', $message);

            // rethrow exception to be caught by controller
            throw $e;
        }
    }

    /**
     * @param Document $document
     *
     * @return bool true if delete is successful
     *
     * @throws \Exception if the document doesn't exist (in addition to S3 network/access failures
     */
    private function deleteFromS3(DocumentInterface $document)
    {
        $ref = $document->getStorageReference();
        if (!$ref) {
            $this->log('notice', 'empty file reference for document '.$document->getId().', cannot delete');
            throw new \Exception('Document could not be removed. No Reference.');
        }

        $this->log('notice', "Deleting $ref from S3");
        $result = $this->s3Storage->removeFromS3($ref);

        $this->log('notice', "Deleting for $ref from S3: no exception thrown from deleteObject operation");

        return $result;
    }

    /**
     * Log message using the internal logger.
     */
    private function log($level, $message)
    {
        // echo $message."\n"; //enable for debugging reasons. Tail the log with log-level=info otherwise

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
    public function retrieveDocumentsFromS3ByReportSubmission(ReportSubmission $reportSubmission)
    {
        $retrievedDocuments = [];
        $missingDocuments = [];

        foreach ($reportSubmission->getDocuments() as $document) {
            try {
                // AWS returns a object here - typecasting to string
                $contents = (string) $this->s3Storage->retrieve($document->getStorageReference());

                $retrievedDocument = new RetrievedDocument();
                $retrievedDocument->setContent($contents);
                $retrievedDocument->setFileName($document->getFileName());
                $retrievedDocument->setReportSubmission($reportSubmission);

                $retrievedDocuments[] = $retrievedDocument;
            } catch (FileNotFoundException $e) {
                $missingDocument = new MissingDocument();
                $missingDocument->setFileName($document->getFileName());
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
     * @param []ReportSubmission $reportSubmissions
     *
     * @return array
     */
    public function retrieveDocumentsFromS3ByReportSubmissions(array $reportSubmissions)
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
     * @param []MissingDocument $missingDocuments
     *
     * @return string
     */
    public function createMissingDocumentsFlashMessage(array $missingDocuments)
    {
        return $this->twig->render(
            '@App/FlashMessages/missing-documents.html.twig',
            ['missingDocuments' => $missingDocuments]
        );
    }
}
