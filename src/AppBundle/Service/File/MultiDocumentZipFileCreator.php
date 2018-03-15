<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\File\Storage\StorageInterface;
use ZipArchive;

class MultiDocumentZipFileCreator
{
    const MSG_NOTHING_DOWNLOADABLE = 'There were no downloadable documents';

    /**
     * @var array
     */
    private $reportSubmissions;

    /**
     * @var StorageInterface
     */
    private $s3Storage;

    /**
     * @var string
     */
    private $zipFile;

    /**
     * @var array
     */
    private $documents = [];

    /**
     * MultiDocumentsZipFileCreator constructor.
     *
     * @param $s3Storage
     * @param array $reportSubmissions array of report submissions
     *
     * @return string
     */
    public function __construct(StorageInterface $s3Storage, array $reportSubmissions)
    {
        $this->reportSubmissions = $reportSubmissions;
        $this->s3Storage = $s3Storage;
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function createZipFile()
    {
        $documents = [];

        /** @var ReportSubmission $reportSubmission */
        foreach ($this->reportSubmissions as $reportSubmission) {
            try {
                $zipFileCreator = new DocumentsZipFileCreator($reportSubmission, $this->s3Storage);
                $filename = $zipFileCreator->createZipFile();

                //store the filename and creator for later use (creator is saved so we can clean up after ourselves)
                $documents[] = [
                    'creator' => $zipFileCreator,
                    'filename' => $filename
                ];
            } catch (\Exception $e) {
                //for now using try/catch to skip reports not downloadable, may want to make this more intelligent later
            }
        }

        //check we at least some downloadable files
        if (empty($documents)) {
            throw new \Exception(self::MSG_NOTHING_DOWNLOADABLE);
        }

        $parentFilename = self::createZipFilePath();

        $zip = new ZipArchive();
        $zip->open(
            $parentFilename,
            ZipArchive::CREATE | ZipArchive::OVERWRITE | ZipArchive::CHECKCONS
        );

        //add each individual zipped report into the main zip file
        foreach ($documents as $document) {
            $zip->addFile($document['filename'], basename($document['filename']));
        }

        $zip->close();
        $this->zipFile = $parentFilename;
        $this->documents = $documents;

        return $parentFilename;
    }

    public function cleanUp()
    {
        if ($this->zipFile && file_exists($this->zipFile)) {
            unlink($this->zipFile);
        }

        //clean up individual reports
        foreach ($this->documents as $document) {
            /** @var DocumentsZipFileCreator $zipCreator */
            $zipCreator = $document['creator'];
            $zipCreator->cleanUp();
        }
    }

    /**
     * @return string
     */
    private static function createZipFilePath()
    {
        return DocumentsZipFileCreator::TMP_ROOT_PATH . '/multidownload-' . microtime(1) . '.zip';
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
