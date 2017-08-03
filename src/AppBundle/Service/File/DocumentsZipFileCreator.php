<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\File\Storage\StorageInterface;
use ZipArchive;

class DocumentsZipFileCreator
{
    const TMP_ROOT_PATH = '/tmp/';

    /**
     * @var ReportSubmission
     */
    private $reportSubmission;

    /**
     * @var StorageInterface
     */
    private $s3Storage;

    /**
     * @var string
     */
    private $zipFile;

    /**
     * DocumentsZipFileCreator constructor.
     * @param $reportSubmission
     * @param $s3Storage
     */
    public function __construct(ReportSubmission $reportSubmission, StorageInterface $s3Storage)
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
            $dfile = self::createDocumentTmpFilePath($document);
            file_put_contents($dfile, $content);
            unset($content);
            $filesToAdd[$document->getFileName()] = $dfile;
        }

        // create ZIP files and add previously-stored uploaded documents
        $filename = self::createZipFilePath($this->reportSubmission);
        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS);
        foreach ($filesToAdd as $localname => $filePath) {
            $zip->addFile($filePath, $localname);
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
        if ($this->zipFile && file_exists($this->zipFile)) {
            unlink($this->zipFile);
        }
    }

    /**
     * @param Document $document
     * @return string
     */
    private static function createDocumentTmpFilePath(Document $document)
    {
        return self::TMP_ROOT_PATH . 'dd_temp_zip_' . $document->getId() . microtime(1);
    }

    /**
     * @param ReportSubmission $reportSubmission
     * @return string
     */
    private static function createZipFilePath(ReportSubmission $reportSubmission)
    {
        return self::TMP_ROOT_PATH . $reportSubmission->getZipName();
    }


    public function __destruct()
    {
        $this->cleanUp();
    }
}
