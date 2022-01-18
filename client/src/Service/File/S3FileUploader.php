<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\ReportInterface;
use App\Service\Client\RestClient;
use App\Service\File\Storage\StorageInterface;
use App\Service\Time\DateTimeProvider;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploader
{
    private StorageInterface $storage;
    private RestClient $restClient;
    private array $options;
    private FileNameFixer $fileNameFixer;

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
     */
    public function uploadSupportingFilesAndPersistDocuments(array $uploadedFiles, Report $report): void
    {
        foreach ($uploadedFiles as $uploadedFile) {
            [$body, $fileName] = $this->getFileBodyAndFileName($uploadedFile);
            $this->uploadFileAndPersistDocument($report, $body, $fileName, false);
        }
    }

    private function getFileBodyAndFileName(UploadedFile $file): array
    {
        /** @var string $body */
        $body = file_get_contents($file->getPathname());

        $fileName = $this->fileNameFixer->addMissingFileExtension($file, $body);
        $fileName = $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension($fileName);
        $fileName = $this->fileNameFixer->removeUnusualCharacters($fileName);

        return [$body, $fileName];
    }

    /**
     * Uploads a file into S3 + create and persist a Document entity using that reference.
     *
     * @return Document
     */
    public function uploadFileAndPersistDocument(ReportInterface $report, string $body, string $fileName, bool $isReportPdf)
    {
        $storageReference = sprintf(
            'dd_doc_%s_%s%s',
            $report->getId(),
            $this->dateTimeProvider->getDateTime()->format('U'),
            // Append milliseconds to ensure the storage reference is unique
            $this->dateTimeProvider->getDateTime()->format('v')
        );

        $this->storage->store($storageReference, $body);

        $document = (new Document())
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setIsReportPdf($isReportPdf);

        $reportType = $report instanceof Report ? 'report' : 'ndr';
        $response = $this->persistDocument($reportType, intval($report->getId()), $document);

        $document->setId($response['id'] ?? null);

        return $document;
    }

    private function persistDocument(string $reportType, int $reportId, Document $document)
    {
        return $this->restClient->post("/document/{$reportType}/{$reportId}", $document, ['document']);
    }

    /**
     * Removes a file from S3.
     *
     * @throws Exception
     */
    public function removeFileFromS3(Document $document)
    {
        $storageReference = $document->getStorageReference();
        if (empty($storageReference)) {
            throw new Exception('Document could not be removed. No Reference.');
        }

        $this->storage->removeFromS3($storageReference);
    }
}
