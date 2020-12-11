<?php declare(strict_types=1);

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\StorageInterface;
use AppBundle\Service\Time\DateTimeProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploader
{
    private StorageInterface $storage;
    private RestClient $restClient;
    private array $options;
    private FileNameFixer $fileNameFixer;
    /**
     * @var DateTimeProvider
     */
    private DateTimeProvider $dateTimeProvider;

    public function __construct(
        StorageInterface $s3Storage,
        RestClient $restClient,
        FileNameFixer $fileNameFixer,
        DateTimeProvider $dateTimeProvider,
        array $options = []
    ) {
        $this->storage = $s3Storage;
        $this->restClient = $restClient;
        $this->fileNameFixer = $fileNameFixer;
        $this->options = $options;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     * @param Report $report
     */
    public function uploadSupportingFilesAndPersistDocuments(array $uploadedFiles, Report $report): void
    {
        foreach ($uploadedFiles as $uploadedFile) {
            [$body, $fileName] = $this->getFileBodyAndFileName($uploadedFile);
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

        $fileName = $this->fileNameFixer->addMissingFileExtension($file, $body);
        $fileName = $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension($fileName);

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
        $storageReference = sprintf('dd_doc_%s_%s', $report->getId(), $this->dateTimeProvider->getDateTime()->format('U'));

        $this->storage->store($storageReference, $body);

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
    }
}
