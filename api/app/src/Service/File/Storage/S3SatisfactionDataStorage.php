<?php

namespace App\Service\File\Storage;

use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

/**
 * Class to upload/download/delete files from S3.
 *
 * Original logic
 * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
 */
class S3SatisfactionDataStorage extends S3Storage
{
    /**
     * S3SatisfactionDataStorage constructor.
     *
     * https://github.com/aws/aws-sdk-php
     * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html.
     *
     * for fake s3:
     * https://github.com/jubos/fake-s3
     * https://github.com/jubos/fake-s3/wiki/Supported-Clients
     */
    public function __construct(S3Client $s3Client, string $bucketName, LoggerInterface $logger)
    {
        parent::__construct($s3Client, $bucketName, $logger);
    }
}
