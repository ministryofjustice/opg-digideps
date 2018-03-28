<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

class DocumentService
{

    /**
     * @var S3Storage
     */
    private $s3Storage;


    /**
     * @var S3Client
     */
    private $s3Client;

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
     * @param S3Client        $s3Client
     * @param RestClient      $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(S3Storage $s3Storage, S3Client $s3Client, RestClient $restClient, LoggerInterface $logger)
    {
        $this->s3Storage = $s3Storage;
        $this->s3Client = $s3Client;
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * Clean up old report submissions (set downloadable = false and set to null the storageReference of the documents)
     *
     * @param bool $ignoreS3Failure
     */
    public function removeOldReportSubmissions($ignoreS3Failure)
    {
        $reportSubmissions = $this->restClient->apiCall('GET', 'report-submission/old', null, 'Report\ReportSubmission[]', [], false);
        $toDelete = count($reportSubmissions);
        $this->log('notice', "$toDelete old report submission found");
        foreach ($reportSubmissions as $reportSubmission) {
            try {
                $reportSubmissionId = $reportSubmission->getId();
                // remove documents from S3
                foreach ($reportSubmission->getDocuments() as $document) {
                    $this->deleteFromS3($document, $ignoreS3Failure);
                }
                // set report as undownloadable
                $this->restClient->apiCall('PUT', 'report-submission/' . $reportSubmissionId . '/set-undownloadable', null, 'array', [], false);
                $this->log('notice', "report submission $reportSubmissionId set undownloadable, and its documents storage ref set to null");
            } catch (\Exception $e) {
                $message = "can't cleanup $reportSubmissionId submission. Error: " . $e->getMessage();
                $this->log('error', $message);
            }
        }
        $this->log('notice', 'Done');
    }

    /**
     * @param bool $ignoreS3Failure
     */
    public function removeSoftDeleted($ignoreS3Failure)
    {
        $documents = $this->restClient->apiCall('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false);
        $toDelete = count($documents);
        $count = 0;
        /* @var $documents Document[] */
        $this->log('notice', count($documents) . ' documents to delete:');
        foreach ($documents as $document) {
            $count += $this->removeSoftDeleteSingle($document, $ignoreS3Failure) ? 1 : 0;
        }

        $this->log('notice', "Done. $toDelete to hard-delete, $count deleted");
    }

    /**
     * @param Document $document
     * @param $ignoreS3Failure
     *
     * @return bool true if deleted from S3 and database
     */
    private function removeSoftDeleteSingle(Document $document, $ignoreS3Failure)
    {
        $documentId = $document->getId();
        $storageRef = $document->getStorageReference();

        try {
            $s3Result = $this->deleteFromS3($document, $ignoreS3Failure);

            $endpointResult = $this->restClient->apiCall('DELETE', 'document/hard-delete/' . $document->getId(), null, 'array', [], false);
            if ($endpointResult) {
                $this->log('notice', "Document $documentId (s3 ref $storageRef) deleted successfully from db");
            } else {
                $this->log('error', "Document $documentId delete API failure");
            }

            return $s3Result && $endpointResult;
        } catch (\Exception $e) {
            $message = "can't delete $documentId, ref $storageRef. Error: " . $e->getMessage();
            $this->log('error', $message);
        }
    }

    /**
     * @param  Document   $document
     * @param  bool       $ignoreS3Failure
     * @throws \Exception if the document doesn't exist (in addition to S3 network/access failures
     * @return bool       true if delete is successful
     *
     */
    private function deleteFromS3(Document $document, $ignoreS3Failure)
    {
        $ref = $document->getStorageReference();
        if (!$ref) {
            $this->log('notice', 'empty file reference for document ' . $document->getId() . ", can't delete");

            return true;
        }

        try {
            $this->appendTagToS3Object($document, ['Key' => 'purge', 'Value' => 1]);

            $this->s3Storage->delete($ref);
            $this->log('notice', "deleting $ref from S3: no exception thrown from deleteObject operation");

            return true;
        } catch (\Exception $e) {
            $this->log('error', "deleting $ref from S3: exception (" . ($ignoreS3Failure ? '(ignored)' : '') . ' ' . $e->getMessage());
            if (!$ignoreS3Failure) {
                throw $e;
            }
        }
    }

    /**
     * Appends a new TagSet to an S3 object
     *
     * @param Document $document
     * @param array    $newTagset
     * @throws \Exception
     */
    private function appendTagToS3Object(Document $document, $newTagset)
    {
        $ref = $document->getStorageReference();
        if (empty($ref)) {
            throw new \Exception('Invalid Reference: ' . $ref . ' when appending tag');
        }
        foreach ($newTagset as $newTag) {
            if (!(array_key_exists('Key', $newTag) && array_key_exists('Value', $newTag))) {
                throw new \Exception('Invalid Tagset updating: ' . $ref . var_export($newTagset));
            }
        }

        $bucketName = $this->getContainer()->getParameter('s3_bucket_name');
        // add purge tag to signal permanent deletion See: DDPB-2010/OPGOPS-2347
        // get the objects tags and then append with PUT
        $existingTags = $this->s3Client->getObjectTagging([
            'Bucket' => $bucketName,
            'Key' => $ref
        ]);

        $newTagset = array_merge($existingTags['TagSet'], ['Key' => 'purge', 'Value' => 1]);

        // Update tags in S3
        $this->s3Client->putObjectTagging([
            'Bucket' => $bucketName,
            'Key' => $ref,
            'Tagging' => [
                'TagSet' => $newTagset
            ],
        ]);
        $this->log('notice', "Updating $ref from S3: No exception updating tags: " . var_export($newTagset));
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
}
