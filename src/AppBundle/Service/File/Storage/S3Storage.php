<?php

namespace AppBundle\Service\File\Storage;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

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
     * S3Storage constructor.
     *
     * @param S3Client $s3Client (Aws library)
     * @param $bucketName S3 bucket name
     */
    public function __construct(S3Client $s3Client, $bucketName)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;
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
        return $this->s3Client->deleteObject([
            'Bucket' => $this->bucketName,
            'Key'    => $key
        ]);
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
            'Metadata' => [
            ],
        ]);
    }
}
