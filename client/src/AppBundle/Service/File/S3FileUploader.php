<?php declare(strict_types=1);

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;

class S3FileUploader
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
     * Uploads a file into S3 + create and persist a Document entity using that reference
     *
     * @param ReportInterface $reportId
     * @param string          $body
     * @param string          $fileName
     * @param bool            $isReportPdf
     *
     * @return Document
     */
    public function uploadFile(ReportInterface $report, $body, $fileName, $isReportPdf)
    {
        $reportId = $report->getId();
        $storageReference = 'dd_doc_' . $reportId . '_' . str_replace('.', '', microtime(1));

        $this->storage->store($storageReference, $body);
        $this->logger->debug("FileUploader : stored $storageReference, " . strlen($body) . ' bytes');

        $document = new Document();
        $document
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setIsReportPdf($isReportPdf);

        $reportType = $report instanceof Report ? 'report' : 'ndr';
        $ret = $this->restClient->post("/document/{$reportType}/{$reportId}", $document, ['document']);
        $document->setId($ret['id']);

        return $document;
    }

    /**
     * Removes a file from S3
     * @param Document $document
     * @throws \Exception
     */
    public function removeFileFromS3(Document $document)
    {
        $storageReference = $document->getStorageReference();
        if (empty($storageReference)) {
            throw new \Exception('Document could not be removed. No Reference.');
        }

        $this->storage->removeFromS3($storageReference);
        $this->logger->debug('FileUploader : Removed ' . $storageReference . ' completely from S3');
    }
}
