<?php

namespace AppBundle\Service;

use AppBundle\Entity\DocumentInterface;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\MissingDocument;
use AppBundle\Model\RetrievedDocument;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\FileNotFoundException;
use AppBundle\Service\File\Storage\S3Storage;
use Psr\Log\LoggerInterface;

class DocumentService
{

    /**
     * @var S3Storage
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
     * DocumentService constructor.
     * @param S3Storage       $s3Storage
     * @param RestClient      $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(S3Storage $s3Storage, RestClient $restClient, LoggerInterface $logger)
    {
        $this->s3Storage = $s3Storage;
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * @param Document $document
     *
     * @return bool true if deleted from S3 and database
     */
    public function removeDocumentFromS3(Document $document)
    {
        $documentId = $document->getId();
        $storageRef = $document->getStorageReference();

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
            $message = "can't delete $documentId, ref $storageRef. Error: " . $e->getMessage();
            $this->log('error', $message);

            // rethrow exception to be caught by controller
            throw($e);
        }
    }

    /**
     * @param  Document   $document
     * @throws \Exception if the document doesn't exist (in addition to S3 network/access failures
     * @return bool       true if delete is successful
     *
     */
    private function deleteFromS3(DocumentInterface $document)
    {
        $ref = $document->getStorageReference();
        if (!$ref) {
            $this->log('notice', 'empty file reference for document ' . $document->getId() . ", can't delete");
            throw new \Exception('Document could not be removed. No Reference.');
        }

        $this->log('notice', "Deleting $ref from S3");
        $result = $this->s3Storage->removeFromS3($ref);

        $this->log('notice', "Deleting for $ref from S3: no exception thrown from deleteObject operation");

        return $result;
    }

    /**
     * Log message using the internal logger
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
     * @param ReportSubmission $reportSubmission
     * @return array
     */
    public function retrieveDocumentsFromS3ByReportSubmission(ReportSubmission $reportSubmission)
    {
        $retrievedDocuments = [];
        $missingDocuments = [];

        foreach ($reportSubmission->getDocuments() as $document) {
            try {
                $contents = $this->s3Storage->retrieve($document->getStorageReference());

                $retrievedDocument = new RetrievedDocument();
                $retrievedDocument->setContent($contents);
                $retrievedDocument->setFileName($document->getFileName());
                $retrievedDocument->setReportSubmission($reportSubmission);

                $retrievedDocuments[] = $retrievedDocument;
            } catch(FileNotFoundException $e) {
                $missingDocument = new MissingDocument();
                $missingDocument->setFileName($document->getFileName());
                $missingDocument->setReportSubmission($reportSubmission);

                $missingDocuments[] = $missingDocument;
            }
        }

        return [$retrievedDocuments, $missingDocuments];
    }

    /**
     * When calling this function use the format:
     *
     * [$documents, $missing] = retrieveDocumentsFromS3ByReportSubmissions($reportSubmissions);
     *
     * See retrieveDocumentsFromS3ByReportSubmission() docblock for background.
     *
     * @param []ReportSubmission $reportSubmissions
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
     * @return string
     */
    public function createMissingDocumentsFlashMessage(array $missingDocuments)
    {
        $bullets = '<ul>';

        foreach($missingDocuments as $missingDocument) {
            $caseNumber = $missingDocument->getReportSubmission()->getCaseNumber();
            $fileName = $missingDocument->getFileName();

            $bullets .= "<li>${caseNumber} - ${fileName}</li>";
        }

        $bullets .= '</ul>';

        return <<<FLASH
The following documents could not be downloaded:
$bullets
FLASH;

    }
}
