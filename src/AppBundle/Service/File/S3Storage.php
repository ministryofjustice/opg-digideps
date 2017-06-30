<?php

namespace AppBundle\Service\File;

use \Aws\S3\S3Client;

/**
 * Class to upload/download/delete files from S3
 * // using logic from https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
 *
 */
class S3Storage
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
     * @var
     */
    private $bucketName;

    /**
     * S3Storage constructor.
     * @param S3Client $s3Client
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
     * @return string file content
     */
    public function retrieve($key)
    {
        $cmd = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key'    => $key
        ]);
        $request = $this->s3Client->createPresignedRequest($cmd, '+20 minutes');

        $url = (string) $request->getUri();

        if (substr(get_headers($url)[0], 9, 3) == 404) {
            throw new FileNotFoundException();
        }

        return file_get_contents($url);
    }

    /**
     * @param string $key
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