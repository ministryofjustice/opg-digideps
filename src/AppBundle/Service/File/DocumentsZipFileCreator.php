<?php

namespace AppBundle\Service\File;

use ZipArchive;

class DocumentsZipFileCreator
{
    const TMP_ROOT_PATH = '/tmp/';

    private $reportSubmission;
    private $s3Storage;
    private $zipFile;

    /**
     * DocumentsZipFileCreator constructor.
     * @param $reportSubmission
     * @param $s3Storage
     */
    public function __construct($reportSubmission, $s3Storage)
    {
        $this->reportSubmission = $reportSubmission;
        $this->s3Storage = $s3Storage;
    }

    public function createZipFile()
    {
        // store files locally, for subsequent memory-less ZIP creation
        $filesToAdd = [];
        if (empty($this->reportSubmission->getDocuments())) {
            throw new \RuntimeException('No documents found for downloading');
        }
        foreach ($this->reportSubmission->getDocuments() as $document) {
            $content = $this->s3Storage->retrieve($document->getStorageReference()); //might throw exception
            $dfile = self::TMP_ROOT_PATH . 'DDDocument' . $document->getId() . microtime(1);
            file_put_contents($dfile, $content);
            unset($content);
            $filesToAdd[$document->getFileName()] = $dfile;
        }

        // create ZIP files and add previously-stored uploaded documents
        $filename = self::TMP_ROOT_PATH . $this->reportSubmission->getZipName();
        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);
        foreach ($filesToAdd as $localname => $filePath) {
            $zip->addFile($filePath, $localname); // addFromString crashes
        }
        $zip->close();
        unset($zip);

        // clean up temp files, as the ZIP has already been created
        foreach ($filesToAdd as $f) {
            unlink($f);
        }

        $this->zipFile = $filename;

        return $filename;
    }

    /**
     * remove temporarly files
     */
    public function cleanUp()
    {
        if (file_exists($this->zipFile)) {
            unlink($this->zipFile);
        }
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
