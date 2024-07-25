<?php

declare(strict_types=1);

namespace App\Service\File;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\ReportInterface;
use App\Exception\MimeTypeAndFileExtensionDoNotMatchException;
use App\Service\Client\RestClient;
use App\Service\File\Storage\StorageInterface;
use App\Service\Time\DateTimeProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploader
{
    public function __construct(
        private StorageInterface $s3Storage,
        private RestClient $restClient,
        private FileNameFixer $fileNameFixer,
        private DateTimeProvider $dateTimeProvider,
        private MimeTypeAndExtensionChecker $mimeTypeAndExtensionChecker,
        private ImageConvertor $imageConvertor,
        private array $options = []
    ) {
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    public function uploadSupportingFilesAndPersistDocuments(array $uploadedFiles, Report $report): void
    {
        foreach ($uploadedFiles as $uploadedFile) {
            $fileBody = file_get_contents($uploadedFile->getRealPath());
            $extensionAndMimeTypeMatch = $this->mimeTypeAndExtensionChecker->check($uploadedFile, $fileBody);

            if (!$extensionAndMimeTypeMatch) {
                throw new MimeTypeAndFileExtensionDoNotMatchException('Your file type and file extension do not match');
            }

            $sanitisedFileName = $this->getSanitisedFileName($uploadedFile);

            [$newBody, $newFilename] = $this->imageConvertor->convert($sanitisedFileName, $uploadedFile->getRealPath());

            $this->uploadFileAndPersistDocument($report, $newBody, $newFilename, false);
        }
    }

    private function getSanitisedFileName(UploadedFile $file): string
    {
        $sanitisedFileNameAndPath = $this->fileNameFixer->addMissingFileExtension($file);
        $sanitisedFileNameAndPath = $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension($sanitisedFileNameAndPath);

        return $this->fileNameFixer->removeUnusualCharacters($sanitisedFileNameAndPath);
    }

    /**
     * Uploads a file into S3 + create and persist a Document entity using that reference.
     *
     * @return Document
     */
    public function uploadFileAndPersistDocument(
        ReportInterface $report, string $body, string $fileName, bool $isReportPdf, bool $overwrite = false
    ) {
        $storageReference = sprintf(
            'dd_doc_%s_%s%s',
            $report->getId(),
            $this->dateTimeProvider->getDateTime()->format('U'),
            // Append milliseconds to ensure the storage reference is unique
            $this->dateTimeProvider->getDateTime()->format('v')
        );

        $this->s3Storage->store($storageReference, $body);

        $document = (new Document())
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setIsReportPdf($isReportPdf);

        $reportType = $report instanceof Report ? 'report' : 'ndr';

        if ($overwrite) {
            $response = $this->persistDocumentOverwrite($reportType, intval($report->getId()), $document);
        } else {
            $response = $this->persistDocument($reportType, intval($report->getId()), $document);
        }

        $document->setId($response['id'] ?? null);

        return $document;
    }

    private function persistDocument(string $reportType, int $reportId, Document $document)
    {
        return $this->restClient->post("/document/{$reportType}/{$reportId}", $document, ['document']);
    }

    private function persistDocumentOverwrite(string $reportType, int $reportId, Document $document)
    {
        return $this->restClient->post("/document/{$reportType}/{$reportId}/overwrite", $document, ['document']);
    }

    /**
     * Removes a file from S3.
     *
     * @throws \Exception
     */
    public function removeFileFromS3(Document $document)
    {
        $storageReference = $document->getStorageReference();
        if (empty($storageReference)) {
            throw new \Exception('Document could not be removed. No Reference.');
        }

        $this->s3Storage->removeFromS3($storageReference);
    }
}
