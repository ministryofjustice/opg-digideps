<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use ZipArchive;

class DocumentsZipFileCreator
{
    const TMP_ROOT_PATH = '/tmp/';

    /**
     * @var array
     */
    private $zipFiles;

    public function __construct()
    {
        $this->zipFiles = [];
    }

    /**
     * @param []RetrievedDocument $retrievedDocuments
     * @return array
     */
    public function createZipFilesFromRetrievedDocuments(array $retrievedDocuments)
    {
        // store files locally, for subsequent memory-less ZIP creation
        $filesToAdd = [];

        $zip = new ZipArchive();

        foreach ($retrievedDocuments as $retrievedDocument) {
            // create ZIP files and add previously-stored uploaded documents
            $localZipFileName = self::createZipFilePath($retrievedDocument->getReportSubmission()->getZipName());

            if (!in_array($localZipFileName, $this->zipFiles)) {
                $this->zipFiles[] = $localZipFileName;
            }

            $zip->open($localZipFileName, ZipArchive::CREATE | ZipArchive::CHECKCONS);

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
     * @param array $zipFiles
     * @return string
     */
    public function createMultiZipFile(array $zipFiles)
    {
        $parentFilename = self::createMultiZipFilePath();

        $zip = new ZipArchive();
        $zip->open(
            $parentFilename,
            ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS
        );

        //add each individual zipped report into the main zip file
        foreach ($zipFiles as $zipFile) {
            $zip->addFile($zipFile, basename($zipFile));
        }

        $zip->close();
        $this->zipFiles[] = $parentFilename;

        return $parentFilename;
    }

    /**
     * remove temporary files
     */
    public function cleanUp()
    {
        if (!empty($this->zipFiles)) {
            foreach($this->zipFiles as $zipfile) {
                if (file_exists($zipfile)) {
                    unlink($zipfile);
                }
            }
        }
    }

    /**
     * @param  Document $document
     * @return string
     */
    private static function createDocumentTmpFilePath(string $fileName)
    {
        return self::TMP_ROOT_PATH . 'dd_temp_zip_' . $fileName . microtime(1);
    }

    /**
     * @param string $zipFileName
     * @return string
     */
    private static function createZipFilePath(string $zipFileName)
    {
        return self::TMP_ROOT_PATH . $zipFileName;
    }

    /**
     * @return string
     */
    private static function createMultiZipFilePath()
    {
        return self::TMP_ROOT_PATH . 'multidownload-' . microtime(1) . '.zip';
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
