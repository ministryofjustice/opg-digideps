<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\File;

use OPG\Digideps\Frontend\Model\RetrievedDocument;

class DocumentsZipFileCreator
{
    public const string TMP_ROOT_PATH = '/tmp/';

    /** @var array<string> filenames of generated zip files */
    private array $zipFiles = [];

    /**
     * @param array<RetrievedDocument> $retrievedDocuments
     *
     * @return array<string>
     */
    public function createZipFilesFromRetrievedDocuments(array $retrievedDocuments): array
    {
        // store files locally, for subsequent memory-less ZIP creation
        $filesToAdd = [];

        $zip = new \ZipArchive();

        foreach ($retrievedDocuments as $retrievedDocument) {
            // create ZIP files and add previously-stored uploaded documents
            $localZipFileName = self::createZipFilePath($retrievedDocument->getReportSubmission()->getZipName());

            if (!in_array($localZipFileName, $this->zipFiles)) {
                $this->zipFiles[] = $localZipFileName;
            }

            $zip->open($localZipFileName, \ZipArchive::CREATE | \ZipArchive::CHECKCONS);

            $document = self::createDocumentTmpFilePath($retrievedDocument->getFileName());
            file_put_contents($document, $retrievedDocument->getContent());
            $zip->addFile($document, $retrievedDocument->getFileName());

            $filesToAdd[] = $document;
        }

        $zip->close();
        unset($zip);

        // clean up temp files, as the ZIP has already been created
        foreach ($filesToAdd as $file) {
            unlink($file);
        }

        return $this->zipFiles;
    }

    /**
     * @return string filename of single generated zip file
     */
    public function createMultiZipFile(array $zipFiles): string
    {
        $parentFilename = self::createMultiZipFilePath();

        $zip = new \ZipArchive();
        $zip->open(
            $parentFilename,
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE | \ZipArchive::CHECKCONS
        );

        // add each individual zipped report into the main zip file
        foreach ($zipFiles as $zipFile) {
            $zip->addFile($zipFile, basename($zipFile));
        }

        $zip->close();
        $this->zipFiles[] = $parentFilename;

        return $parentFilename;
    }

    /**
     * Remove temporary files.
     */
    public function cleanUp(): void
    {
        foreach ($this->zipFiles as $zipFile) {
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }
        }
    }

    private static function createDocumentTmpFilePath(string $fileName): string
    {
        return self::TMP_ROOT_PATH . 'dd_temp_zip_' . $fileName . microtime(true);
    }

    private static function createZipFilePath(string $zipFileName): string
    {
        return self::TMP_ROOT_PATH . $zipFileName;
    }

    private static function createMultiZipFilePath(): string
    {
        return self::TMP_ROOT_PATH . 'multidownload-' . microtime(true) . '.zip';
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
