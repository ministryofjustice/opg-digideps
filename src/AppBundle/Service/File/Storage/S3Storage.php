<?php

namespace AppBundle\Service\File\Storage;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;

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
    public function __construct(S3ClientInterface $s3Client, $bucketName)
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
        $this->appendTagset($key, [['Key' => 'purge', 'Value' => 1]]);

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
    public function appendTagset($key, $newTagset) {
        if (empty($key)) {
            throw new \Exception('Invalid Reference Key: ' . $key . ' when appending tag');
        }
        foreach ($newTagset as $newTag) {
            if (!(array_key_exists('Key', $newTag) && array_key_exists('Value', $newTag))) {
                throw new \Exception('Invalid Tagset updating: ' . $key . var_export($newTagset));
            }
        }

        // add purge tag to signal permanent deletion See: DDPB-2010/OPGOPS-2347
        // get the objects tags and then append with PUT

        $existingTags = $this->s3Client->getObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $key
        ]);

        // Update tags in S3
        $this->s3Client->putObjectTagging([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Tagging' => [
                'TagSet' => array_merge($existingTags['TagSet'], $newTagset)
            ],
        ]);
    }
}
