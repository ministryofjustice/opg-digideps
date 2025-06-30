<?php

namespace App\Service\File\Storage;

use Aws\Result;
use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Psr\Log\LoggerInterface;

/**
 * Class to upload/download/delete files from S3.
 *
 * Original logic
 * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
 */
class ClientS3Storage
{
    // If a file is deleted in S3 it will return an AccessDenied error until its permanently deleted
    public const MISSING_FILE_AWS_ERROR_CODES = ['NoSuchKey', 'AccessDenied'];

    /**
     * S3Storage constructor.
     *
     * https://github.com/aws/aws-sdk-php
     * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html.
     *
     * for fake s3:
     * https://github.com/jubos/fake-s3
     * https://github.com/jubos/fake-s3/wiki/Supported-Clients
     */
    public function __construct(
        private S3Client $s3Client,
        private string $bucketName,
        private LoggerInterface $logger,
    ) {
    }

    public function retrieve(string $key): string
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            /** @var Stream $stream */
            $stream = $result['Body'];

            return $stream->read($stream->getSize() ?? 0);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), self::MISSING_FILE_AWS_ERROR_CODES)) {
                throw new FileNotFoundException("Cannot find file with reference $key");
            }
            throw $e;
        }
    }

    public function removeFromS3(string $key): array
    {
        if (empty($key)) {
            throw new \RuntimeException('Could not remove file: Document not specified');
        } else {
            /*
             * ListObjectVersions is permitted by ListBucketVersions in IAM.
             */
            $objectVersions = $this->s3Client->listObjectVersions([
                'Bucket' => $this->bucketName,
                'Prefix' => $key,
            ]);

            if (!$objectVersions instanceof ResultInterface || !$objectVersions->hasKey('Versions')) {
                throw new \RuntimeException('Could not remove file: No results returned');
            } else {
                $objectVersions = $objectVersions->toArray();

                $objectsToDelete = $this->prepareObjectsToDelete($objectVersions);
                if (empty($objectsToDelete)) {
                    throw new \RuntimeException('Could not remove file: No objects founds');
                } else {
                    $s3Result = $this->s3Client->deleteObjects([
                        'Bucket' => $this->bucketName,
                        'Delete' => ['Objects' => $objectsToDelete],
                    ]);
                    $s3Result = $s3Result->toArray();

                    $this->handleS3DeletionErrors($s3Result);
                }

                return $this->logS3Results($objectVersions, $objectsToDelete, $s3Result);
            }
        }
    }

    private function logS3Results(array $objectVersions, array $objectsToDelete, array $s3Result): array
    {
        $resultsSummary = [
            'objectVersions' => $objectVersions,
            'objectsToDelete' => $objectsToDelete,
            'results' => [
                's3Result' => $s3Result,
            ],
        ];

        $this->log('info', json_encode($resultsSummary) ?: 'result summary was not JSON encoded');

        return $resultsSummary;
    }

    /**
     * Extracts and returns new array structure from AwsResults array detailing objects to remove from S3.
     */
    private function prepareObjectsToDelete(array $objectVersions): array
    {
        $objectsToDelete = [];
        if (array_key_exists('Versions', $objectVersions)) {
            foreach ($objectVersions['Versions'] as $versionData) {
                if (strlen($versionData['VersionId']) > 0) {
                    $objectsToDelete[] = [
                        'Key' => $versionData['Key'],
                        'VersionId' => $versionData['VersionId'],
                    ];
                }
            }
        }

        return $objectsToDelete;
    }

    private function handleS3DeletionErrors(array $s3Result): void
    {
        if (array_key_exists('Errors', $s3Result) && count($s3Result['Errors']) > 0) {
            foreach ($s3Result['Errors'] as $s3Error) {
                $this->log(
                    'error',
                    'Unable to remove file from S3 - Key: '.$s3Error['Key'].', VersionId: '.
                        $s3Error['VersionId'].', Code: '.$s3Error['Code'].', Message: '.$s3Error['Message']
                );
            }

            $this->log(
                'error',
                'Unable to remove key from S3: '.(json_encode($s3Result['Errors']) ?: 'could not JSON encode errors')
            );

            throw new \RuntimeException('Could not remove files: '.(json_encode($s3Result['Errors']) ?: 'could not JSON encode errors'));
        }
    }

    public function store(string $key, string $body): Result
    {
        $response = $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Body' => $body,
            'ServerSideEncryption' => 'AES256',
            'Metadata' => [],
        ]);

        $this->s3Client->waitUntil('ObjectExists', [
            'Bucket' => $this->bucketName,
            'Key' => $key,
        ]);

        if (!$this->s3Client->doesObjectExistV2($this->bucketName, $key)) {
            $this->log('error', 'Failed to upload file to S3. Filename: '.$key);

            throw new FileUploadFailedException($key);
        }

        return $response;
    }

    /**
     * Log message using the internal logger.
     */
    private function log(string $level, string $message): void
    {
        $this->logger->log($level, $message, ['extra' => [
            'service' => 's3-storage',
        ]]);
    }

    // check if file exists in S3 bucket
    public function checkFileExistsInS3(string $key): bool
    {
        if ($this->s3Client->doesObjectExistV2($this->bucketName, $key)) {
            return true;
        }

        return false;
    }
}
