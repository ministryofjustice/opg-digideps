<?php

namespace App\Service\File\Storage;

use Aws\Result;
use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Psr7\Stream;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class to upload/download/delete files from S3.
 *
 * Original logic
 * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
 */
class S3Storage implements StorageInterface
{
    // If a file is deleted in S3 it will return an AccessDenied error until its permanently deleted
    public const MISSING_FILE_AWS_ERROR_CODES = ['NoSuchKey', 'AccessDenied'];

    /**
     * S3Storage constructor.
     *
     *
     * https://github.com/aws/aws-sdk-php
     * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html.
     *
     * for fake s3:
     * https://github.com/jubos/fake-s3
     * https://github.com/jubos/fake-s3/wiki/Supported-Clients
     */
    function __construct(
        private readonly S3ClientInterface $s3Client,
        private readonly string $bucketName,
        private readonly LoggerInterface $logger
    ) {}

    public function retrieve(string $key): string
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            /** @var Stream $stream */
            $stream = $result['Body'];

            return $stream->read($stream->getSize());
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), self::MISSING_FILE_AWS_ERROR_CODES)) {
                throw new FileNotFoundException("Cannot find file with reference $key");
            }
            throw $e;
        }
    }

    public function delete($key): Result
    {
        $this->appendTagset($key, [['Key' => 'Purge', 'Value' => 1]]);

        return $this->s3Client->deleteObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
        ]);
    }

    public function removeFromS3(string $key): array
    {
        if (empty($key)) {
            throw new RuntimeException('Could not remove file: Document not specified');
        } else {
            /*
             * ListObjectVersions is permitted by ListBucketVersions in IAM.
             */
            $objectVersions = $this->s3Client->listObjectVersions([
                'Bucket' => $this->bucketName,
                'Prefix' => $key,
            ]);

            if (!$objectVersions instanceof ResultInterface || !$objectVersions->hasKey('Versions')) {
                throw new RuntimeException('Could not remove file: No results returned');
            } else {
                $objectVersions = $objectVersions->toArray();
                $s3Result = [];

                $objectsToDelete = $this->prepareObjectsToDelete($objectVersions);
                if (empty($objectsToDelete)) {
                    throw new RuntimeException('Could not remove file: No objects founds');
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

        $this->log('info', json_encode($resultsSummary));

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

    private function handleS3DeletionErrors(array $s3Result)
    {
        if (array_key_exists('Errors', $s3Result) && count($s3Result['Errors']) > 0) {
            foreach ($s3Result['Errors'] as $s3Error) {
                $this->log('error', 'Unable to remove file from S3 -
                            Key: '.$s3Error['Key'].', VersionId: '.
                    $s3Error['VersionId'].', Code: '.$s3Error['Code'].', Message: '.$s3Error['Message']);
            }
            $this->log('error', 'Unable to remove key from S3: '.json_encode($s3Result['Errors']));
            throw new RuntimeException('Could not remove files: '.json_encode($s3Result['Errors']));
        }
    }

    public function store($key, $body): Result
    {
        $response = $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Body' => $body,
            'ServerSideEncryption' => 'AES256',
            'Metadata' => [],
        ]);

        $this->s3Client->waitUntil('ObjectExists', array(
            'Bucket' => $this->bucketName,
            'Key'    => $key
        ));

        if (!$this->s3Client->doesObjectExistV2($this->bucketName, $key)) {
            $this->log('error', 'Failed to upload file to S3. Filename: '. $key);

            throw new FileUploadFailedException($key);
        }

        return $response;
    }

    /**
     * Appends new tagset to S3 Object.
     *
     * @param $key
     * @param $newTagset
     *
     * @throws \Exception
     */
    public function appendTagset($key, $newTagset)
    {
        $this->log('info', "Appending Purge tag for $key to S3");
        if (empty($key)) {
            throw new \Exception('Invalid Reference Key: '.$key.' when appending tag');
        }
        foreach ($newTagset as $newTag) {
            if (!(array_key_exists('Key', $newTag) && array_key_exists('Value', $newTag))) {
                throw new \Exception('Invalid Tagset updating: '.$key.print_r($newTagset, true));
            }
        }

        // add purge tag to signal permanent deletion See: DDPB-2010/OPGOPS-2347
        // get the objects tags and then append with PUT

        $this->log('info', "Retrieving tagset for $key from S3");
        $existingTags = $this->s3Client->getObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $key,
        ]);

        $newTagset = array_merge($existingTags['TagSet'], $newTagset);
        $this->log('info', "Tagset retrieved for $key : ".print_r($existingTags, true));
        $this->log('info', "Updating tagset for $key with ".print_r($newTagset, true));

        // Update tags in S3
        $this->s3Client->putObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Tagging' => [
                'TagSet' => $newTagset,
            ],
        ]);
        $this->log('info', "Tagset Updated for $key ");
    }

    /**
     * Log message using the internal logger.
     *
     * @param $level
     * @param $message
     */
    private function log($level, $message)
    {
        // echo $message."\n"; //enable for debugging reasons. Tail the log with log-level=info otherwise

        $this->logger->log($level, $message, ['extra' => [
            'service' => 's3-storage',
        ]]);
    }
}
