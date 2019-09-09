<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use ZipArchive;

class DocumentsZipFileCreator
{
    const TMP_ROOT_PATH = '/tmp/';
    const MSG_NOT_DOWNLOADABLE = 'This report is not downloadable';

    /**
     * @var array
     */
    private $zipFiles;

    /**
     * @param array $documentsContents
     * @param ReportSubmission $reportSubmission
     * @return string
     */
    public function createZipFileFromDocumentContents(array $documentsContents, ReportSubmission $reportSubmission)
    {
        // store files locally, for subsequent memory-less ZIP creation
        $filesToAdd = [];

        if ($reportSubmission->isDownloadable() !== true) {
            throw new \RuntimeException(self::MSG_NOT_DOWNLOADABLE);
        }

        if (empty($reportSubmission->getDocuments())) {
            throw new \RuntimeException('No documents found for downloading');
        }

        foreach ($documentsContents as $fileName => $content) {
            $document = self::createDocumentTmpFilePath($fileName);
            file_put_contents($document, $content);
            unset($content);
            $filesToAdd[$fileName] = $document;
        }

        // create ZIP files and add previously-stored uploaded documents
        $zipFileName = self::createZipFilePath($reportSubmission);
        $zip = new ZipArchive();
        $zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);

        foreach ($filesToAdd as $localName => $filePath) {
            $zip->addFile($filePath, $localName);
        }

        $zip->close();
        unset($zip);

        // clean up temp files, as the ZIP has already been created
        foreach ($filesToAdd as $f) {
            unlink($f);
        }

        $this->zipFiles[] = $zipFileName;

        return $zipFileName;
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
     * @param  ReportSubmission $reportSubmission
     * @return string
     */
    private static function createZipFilePath(ReportSubmission $reportSubmission)
    {
        return self::TMP_ROOT_PATH . $reportSubmission->getZipName();
    }

    /**
     * @return string
     */
    private static function createMultiZipFilePath()
    {
        return self::TMP_ROOT_PATH . '/multidownload-' . microtime(1) . '.zip';
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
