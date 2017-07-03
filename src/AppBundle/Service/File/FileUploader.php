<?php

namespace AppBundle\Service\File;


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\StorageInterface;
use AppBundle\Service\File\Checker\FileCheckerInterface;

class FileUploader
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var FileCheckerInterface[]
     */
    private $fileCheckers;

    /**
     * @var array
     */
    private $options;

    /**
     * FileUploader constructor.
     * @param StorageInterface $s3Storage
     */
    public function __construct(StorageInterface $s3Storage, RestClient $restClient, array $options = [])
    {
        $this->storage = $s3Storage;
        $this->restClient = $restClient;
        $this->fileCheckers = [];
        $this->options = [];
    }

    /**
     * @param FileCheckerInterface $fileCheckers
     */
    public function addFileChecker(FileCheckerInterface $fileChecker)
    {
        $this->fileCheckers[] = $fileChecker;
    }


    /**
     * Uploads a file and return the created document
     * might throw exceptions if viruses are found. File is immediately deleted in that case
     *
     * @return Document
     *
     * code imported from
     * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
     */
    public function uploadFile(Report $report, $filename, $filepath)
    {
        $body = file_get_contents($filepath);

        foreach($this->fileCheckers as $fc) {
            $fc->checkFile($body);
        }

        $key = 'dd_doc_' . microtime(1);
        $this->storage->store($key, $body);
        $document = new Document($filename, new \DateTime(), substr($filename, -3));
        //$this->restClient->post('/report/' . $report->getId() . '/document', $document, ['document']);

        return $document;

//
//        $startTime = $_POST['submit_time'] / 1000;
//        //  Determine the filesize allowed
//        $filesize = trim(ini_get('upload_max_filesize'));
//        //  If the filesize includes K, M or G then append a B
//        if (!is_null($filesize) && in_array(substr($filesize, -1), ['K', 'M', 'G'])) {
//            $filesize .= 'B';
//        }
//        //  If this was a post but the upload file isn't present then that means the file was too large
//        if (!is_array($_FILES['upload_file']) || empty($_FILES['upload_file'])) {
//            throw new \Exception('File cannot be larger than ' . $filesize);
//        }
//        $uploadFileArr = $_FILES['upload_file'];
//        //  If the file is empty show an error
//        if ($uploadFileArr['size'] === 0) {
//            throw new \Exception('File cannot be empty');
//        }
//        $uploadFileError = $uploadFileArr['error'];
//        //  Check for errors
//        if ($uploadFileError != UPLOAD_ERR_OK) {
//            switch ($uploadFileError) {
//                case UPLOAD_ERR_INI_SIZE:
//                case UPLOAD_ERR_FORM_SIZE:
//                    throw new \Exception('File cannot be larger than ' . $filesize);
//                    break;
//                case UPLOAD_ERR_NO_FILE:
//                    throw new \Exception('No file selected');
//                    break;
//            }
//            throw new \Exception('There was an error uploading the file, please try again');
//        }
//        //  Upload the file to quarantine folder until we check it's ok
//        $filename = basename($uploadFileArr['name']);
//        $filePath = FILE_UPLOAD_TARGET . $filename;
//        $filenameTemp = $uploadFileArr['tmp_name'];
//        if (!move_uploaded_file($filenameTemp, $filePath)) {
//            throw new \Exception('There was an error uploading the file, please try again');
//        }
    }


}