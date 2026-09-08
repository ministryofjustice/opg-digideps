<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\File;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Exception\MimeTypeAndFileExtensionDoNotMatchException;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\File\Storage\StorageInterface;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileUploader
{
    public function __construct(
        private readonly StorageInterface $s3Storage,
        private readonly RestClient $restClient,
        private readonly FileNameManipulation $fileNameFixer,
        private readonly DateTimeProvider $dateTimeProvider,
        private readonly MimeTypeAndExtensionChecker $mimeTypeAndExtensionChecker,
        private readonly ImageConvertor $imageConvertor,
    ) {
    }

    /**
     * @param UploadedFile[] $uploadedFiles
     */
    public function uploadSupportingFilesAndPersistDocuments(array $uploadedFiles, Report $report): void
    {
        foreach ($uploadedFiles as $uploadedFile) {
            $fileBody = file_get_contents($uploadedFile->getRealPath());

            // check for uppercase extensions and lower if required
            $uploadedFile = FileNameManipulation::lowerCaseFileExtension($uploadedFile);

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

        return FileNameManipulation::fileNameSanitation($sanitisedFileNameAndPath);
    }

    /**
     * Uploads a file into S3 + create and persist a Document entity using that reference.
     */
    public function uploadFileAndPersistDocument(
        Report $report,
        string $body,
        string $fileName,
        bool $isReportPdf,
        bool $overwrite = false
    ): Document {
        $storageReference = sprintf(
            'dd_doc_%s_%s%s',
            $report->getId(),
            $this->dateTimeProvider->getDateTime()->format('U'),
            // Append milliseconds to ensure the storage reference is unique
            $this->dateTimeProvider->getDateTime()->format('v')
        );

        $this->s3Storage->store($storageReference, $body);

        $document = new Document()
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setIsReportPdf($isReportPdf);

        $url = "/document/report/{$report->getId()}";
        if ($overwrite) {
            $url .= "/overwrite";
        }

        $response = $this->restClient->post($url, $document, ['document']);

        $id = new ValidatingArray(is_array($response) ? $response : [])->getIntegerOrThrow('id');
        $document->setId($id);

        return $document;
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
