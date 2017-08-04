<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Checker\FileCheckerInterface;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * Uploads a file and return the created document
     * might throw exceptions if viruses are found. File is immediately deleted in that case
     *
     *
     * @param Report $report
     * @param UploadedFile $uploadedFile
     * @return Document
     */
    public function uploadFile(Report $report, UploadedFile $uploadedFile)
    {
        $body = file_get_contents($uploadedFile->getPathName());

        return $this->saveFileIntoStorageAndDb($report->getId(), $body, $uploadedFile->getClientOriginalName());
    }

    /**
     * @param Report $report
     * @param string $body
     * @return Document
     */
    public function uploadReport(Report $report, $body)
    {
        $fileName = $report->createAttachmentName('DigiRep-%s_%s_%s.pdf');

        return $this->saveFileIntoStorageAndDb($report->getId(), $body, $fileName);
    }

    /**
     *
     * @param integer $reportId
     * @param string $body
     * @param $fileName
     * @return Document
     */
    private function saveFileIntoStorageAndDb($reportId, $body, $fileName)
    {
        $storageReference = 'dd_doc_' . $reportId . '_' . str_replace('.', '', microtime(1));

        $this->storage->store($storageReference, $body);
        $this->logger->debug("FileUploder : stored $storageReference, " . strlen($body)." bytes");

        $document = new Document();
        $document->setStorageReference($storageReference)->setFileName($fileName);
        $this->restClient->post('/report/' . $reportId . '/document', $document, ['document']);

        return $document;
    }
}
