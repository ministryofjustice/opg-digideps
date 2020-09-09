<?php declare(strict_types=1);

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploader
{
    /** @var StorageInterface */
    private $storage;

    /** @var RestClient */
    private $restClient;

    /** @var LoggerInterface */
    private $logger;

    /** @var array */
    private $options;
    private $fileCheckers;

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

    public function uploadFiles(array $files, Report $report): void
    {
        foreach ($files as $file) {
            [$body, $fileName] = $this->getFileBodyAndFileName($file);
            $this->uploadFileAndPersistDocument($report, $body, $fileName, false);
        }
    }

    /**
     * @param UploadedFile $file
     * @return array
     */
    private function getFileBodyAndFileName(UploadedFile $file): array
    {
        /** @var string $body */
        $body = file_get_contents($file->getPathname());

        /** @var string $fileName */
        $fileName = FileNameFixer::removeWhiteSpaceBeforeFileExtension($file->getClientOriginalName());

        return [$body, $fileName];
    }

    /**
     * Uploads a file into S3 + create and persist a Document entity using that reference
     *
     * @param ReportInterface $report
     * @param string $body
     * @param string $fileName
     * @param bool $isReportPdf
     * @return Document
     */
    public function uploadFileAndPersistDocument(ReportInterface $report, string $body, string $fileName, bool $isReportPdf)
    {
        $storageReference = 'dd_doc_' . $report->getId() . '_' . str_replace('.', '', microtime(true));

        $this->storage->store($storageReference, $body);
        $this->logger->debug("FileUploader : stored $storageReference, " . strlen($body) . ' bytes');

        $document = (new Document())
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setIsReportPdf($isReportPdf);

        $reportType = $report instanceof Report ? 'report' : 'ndr';
        $response = $this->persistDocument($reportType, intval($report->getId()), $document);

        $document->setId($response['id']);

        return $document;
    }

    private function persistDocument(string $reportType, int $reportId, Document $document)
    {
        return $this->restClient->post("/document/{$reportType}/{$reportId}", $document, ['document']);
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
