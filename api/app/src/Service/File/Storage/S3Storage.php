<?php

namespace App\Service\File\Storage;

use Aws\Result;
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
class S3Storage
{
    // If a file is deleted in S3 it will return an AccessDenied error until it's permanently deleted
    public const MISSING_FILE_AWS_ERROR_CODES = ['NoSuchKey', 'AccessDenied'];

    /**
     * S3Storage constructor.
     *
     * https://github.com/aws/aws-sdk-php
     * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html.
     */
    public function __construct(
        private readonly S3Client $s3Client,
        private readonly string $bucketName,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Log message using the internal logger.
     */
    private function log(string $level, string $message): void
    {
        $this->logger->log($level, $message."\n", ['extra' => [
            'service' => 's3-storage',
        ]]);
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

    public function tagForDeletion(string $key): bool
    {
        $this->log('warning', "Appending Purge=1 tag for $key to S3");

        // add purge tag to signal permanent deletion See: DDPB-2010/OPGOPS-2347/DDLS-761;
        // get the object's tags and then append with PUT
        try {
            $result = $this->s3Client->getObjectTagging([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);
        } catch (S3Exception $e) {
            $this->log(
                'error',
                "Failed to retrieve tagset for $key (object probably already gone); message = {$e->getMessage()}"
            );

            return false;
        }

        /** @var array $existingTags */
        $existingTags = $result['TagSet'];

        // remove existing Purge=1 tags
        $filteredTags = array_filter($existingTags, fn ($item) => 'Purge' !== $item['Key'] || '1' !== $item['Value']);

        // if filtered tags is different, then we removed a Purge=1 tag from it;
        // this means it is already present on the S3 object, so we don't need to add it and can return now
        if ($filteredTags !== $existingTags) {
            $this->log('warning', "Object $key is already marked with Purge=1 (to be deleted)");

            return true;
        }

        $newTags = array_merge($existingTags, [['Key' => 'Purge', 'Value' => '1']]);
        $this->log('warning', "Tagset retrieved for $key : ".print_r($existingTags, true));

        // Update tags in S3
        try {
            $this->s3Client->putObjectTagging([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'Tagging' => [
                    'TagSet' => $newTags,
                ],
            ]);

            $this->log('warning', "Tagset updated for $key");
        } catch (S3Exception $e) {
            $this->log('error', "Failed to update tagset for $key; message = {$e->getMessage()}");

            return false;
        }

        return true;
    }
}
