<?php

namespace AppBundle\Service\File\Storage;

use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Class to upload/download/delete files from S3
 *
 * Original logic
 * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
 */
class S3Storage implements StorageInterface
{
    /**
     * @var S3Client
     *
     * https://github.com/aws/aws-sdk-php
     * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html
     *
     *
     * for fake s3:
     * https://github.com/jubos/fake-s3
     * https://github.com/jubos/fake-s3/wiki/Supported-Clients
     */
    private $s3Client;

    /**
     * @var string
     */
    private $bucketName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * S3Storage constructor.
     *
     * @param S3Client $s3Client (Aws library)
     * @param $bucketName S3 bucket name
     * @param LoggerInterface $logger
     */
    public function __construct(S3ClientInterface $s3Client, $bucketName, LoggerInterface $logger)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;
        $this->logger = $logger;
    }

    /**
     * Gets file content
     * To download it, use
     * header('Content-Disposition: attachment; filename="' . $_GET['filename'] .'"');
     * readfile(<this method>);
     *
     *
     * @param $bucketName
     * @param $key
     *
     * @throws FileNotFoundException is the file is not found
     *
     * @return string file content
     */
    public function retrieve($key)
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key'    => $key
            ]);

            return $result['Body'];
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                throw new FileNotFoundException("Cannot find file with reference $key");
            }
            throw $e;
        }
    }

    /**
     * @param  string      $key
     * @return \Aws\Result
     */
    public function delete($key)
    {
        $this->appendTagset($key, [['Key' => 'Purge', 'Value' => 1]]);

        return $this->s3Client->deleteObject([
            'Bucket' => $this->bucketName,
            'Key'    => $key
        ]);
    }

    /**
     * Remove an object and all its versions from S3 completely.
     *
     * @param  string      $key
     * @return array
     */
    public function removeFromS3($key)
    {
        if (empty($key)) {
            throw new \RuntimeException('Could not remove file: Document not specified');
        } else {
            /*
             * ListObjectVersions is permitted by ListBucketVersions in IAM.
             */
            $objectVersions = $this->s3Client->listObjectVersions([
                'Bucket' => $this->bucketName,
                'Prefix' => $key
            ]);

            if (!$objectVersions instanceof ResultInterface || !($objectVersions->hasKey('Versions'))) {
                throw new \RuntimeException('Could not remove file: No results returned');
            } else {
                $objectVersions = $objectVersions->toArray();
                $s3Result = [];

                $objectsToDelete = $this->prepareObjectsToDelete($objectVersions);
                if (empty($objectsToDelete)) {
                    throw new \RuntimeException('Could not remove file: No objects founds');
                } else {
                    $s3Result = $this->s3Client->deleteObjects([
                        'Bucket' => $this->bucketName,
                        'Delete' => ['Objects' => $objectsToDelete]
                    ]);
                    $s3Result = $s3Result->toArray();

                    $this->handleS3Errors($s3Result);
                }

                $resultsSummary = $this->logS3Results($objectVersions, $objectsToDelete, $s3Result);

                return $resultsSummary;
            }
        }
    }

    /**
     * Write results information to log
     * @param array $objectVersions
     * @param array $objectsToDelete
     * @param array $s3Result
     * @return array
     */
    private function logS3Results(array $objectVersions, array $objectsToDelete, array $s3Result) {
        $resultsSummary = [
            'objectVersions' => $objectVersions,
            'objectsToDelete' => $objectsToDelete,
            'results' => [
                's3Result' => $s3Result,
            ]
        ];

        $this->log('info', json_encode($resultsSummary));

        return $resultsSummary;
    }

    /**
     * Extracts and returns new array structure from AwsResults array detailing objects to remove from S3
     *
     * @param array $objectVersions
     * @return array
     */
    private function prepareObjectsToDelete(array $objectVersions)
    {
        $objectsToDelete = [];
        /** @var array $objectVersions */
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

    /**
     * Handles any errors returned from S3 SDK. Exceptions that might have been handled by the SDK and converted to
     * an Errors array reutrned
     *
     * @param array $s3Result
     * @throws \RuntimeException
     */
    private function handleS3Errors(array $s3Result)
    {
        if (array_key_exists('Errors', $s3Result) && count($s3Result['Errors']) > 0) {
            foreach ($s3Result['Errors'] as $s3Error) {
                //@todo - check if error thrown due to newline between 203 and 204 not concatenated
                $this->log('error', 'Unable to remove file from S3 - 
                            Key: ' . $s3Error['Key'] . ', VersionId: ' .
                    $s3Error['VersionId'] . ', Code: ' . $s3Error['Code'] . ', Message: ' . $s3Error['Message']);
            }
            $this->log('error', 'Unable to remove key: ' . $s3Result['Errors'] . '  from S3: ' . json_encode($s3Result['Errors']));
            throw new \RuntimeException('Could not remove file: ' . $s3Result['Errors']['Message']);
        }
    }

    /**
     * @param $key
     * @param $body
     * @return \Aws\Result
     */
    public function store($key, $body)
    {
        return $this->s3Client->putObject([
            'Bucket'   => $this->bucketName,
            'Key'      => $key,
            'Body'     => $body,
            'ServerSideEncryption' => 'AES256',
            'Metadata' => []
        ]);
    }

    /**
     * Appends new tagset to S3 Object
     *
     * @param $key
     * @param $newTagset
     * @throws \Exception
     */
    public function appendTagset($key, $newTagset)
    {
        $this->log('info', "Appending Purge tag for $key to S3");
        if (empty($key)) {
            throw new \Exception('Invalid Reference Key: ' . $key . ' when appending tag');
        }
        foreach ($newTagset as $newTag) {
            if (!(array_key_exists('Key', $newTag) && array_key_exists('Value', $newTag))) {
                throw new \Exception('Invalid Tagset updating: ' . $key . print_r($newTagset, true));
            }
        }

        // add purge tag to signal permanent deletion See: DDPB-2010/OPGOPS-2347
        // get the objects tags and then append with PUT

        $this->log('info', "Retrieving tagset for $key from S3");
        $existingTags = $this->s3Client->getObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $key
        ]);

        $newTagset = array_merge($existingTags['TagSet'], $newTagset);
        $this->log('info', "Tagset retrieved for $key : " . print_r($existingTags, true));
        $this->log('info', "Updating tagset for $key with " . print_r($newTagset, true));

        // Update tags in S3
        $this->s3Client->putObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Tagging' => [
                'TagSet' => $newTagset
            ],
        ]);
        $this->log('info', "Tagset Updated for $key ");
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
            'service' => 's3-storage',
        ]]);
    }
}
