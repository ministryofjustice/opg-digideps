<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Checker\FileCheckerInterface;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;

class FileUploader
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var FileCheckerInterface[]
     */
    private $fileCheckers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $options;

    /**
     * FileUploader constructor.
     */
    public function __construct(StorageInterface $s3Storage, RestClient $restClient, LoggerInterface $logger, array $options = [])
    {
        $this->storage = $s3Storage;
        $this->restClient = $restClient;
        $this->logger = $logger;
        $this->fileCheckers = [];
        $this->options = [];
    }

    /**
     * @param FileCheckerInterface $fileCheckers
     */
    public function addFileChecker(FileCheckerInterface $fileChecker)
    {
        $this->fileCheckers[] = $fileChecker;
    }

    /**
     * Uploads a file and return the created document
     * might throw exceptions if viruses are found. File is immediately deleted in that case
     *
     * @return Document
     *
     * code imported from
     * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
     */
    public function uploadFile(Report $report, $filename, $filepath)
    {
        $body = file_get_contents($filepath);

        foreach ($this->fileCheckers as $fc) {
            $fc->checkFile($body);
        }

        $key = 'dd_doc_' . microtime(1);
        $this->storage->store($key, $body);
        $this->logger->debug("Stored file, key = $key, size " . strlen($body));
        $document = new Document($key, $filename, new \DateTime());
        $this->restClient->post('/report/' . $report->getId() . '/document', $document, ['document']);

        return $document;
    }
}
