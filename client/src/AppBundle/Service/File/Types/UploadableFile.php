<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\FileCheckerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableFile implements UploadableFileInterface
{
    protected $scannerEndpoint = 'UNDEFINED';

    /**
     * @var FileCheckerInterface[]
     */
    protected $fileCheckers;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UploadedFile $file
     */
    protected $uploadedFile;

    /**
     * @var array Scan result
     */
    protected $scanResult;

    /**
     * FileUploader constructor.
     */
    public function __construct(
        FileCheckerInterface $virusChecker,
        FileCheckerInterface $fileChecker,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }

    /**
     * @return FileCheckerInterface[]
     */
    public function getFileCheckers()
    {
        return $this->fileCheckers;
    }

    /**
     * @param FileCheckerInterface[] $fileCheckers
     *
     * @return $this
     */
    public function setFileCheckers($fileCheckers)
    {
        $this->fileCheckers = $fileCheckers;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return $this
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function checkFile()
    {
        $this->callFileCheckers();
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function callFileCheckers()
    {
        foreach ($this->getFileCheckers() as $fc) {
            // send file
            $fc->checkFile($this);
        }
    }

    /**
     * @return array
     */
    public function getScanResult()
    {
        return $this->scanResult;
    }

    /**
     * @param array $scanResult
     */
    public function setScanResult($scanResult)
    {
        $this->scanResult = $scanResult;
        return $this;
    }

    /**
     * Is the file safe to upload?
     *
     * @return bool
     */
    public function isSafe()
    {
        $scanResult = $this->getScanResult();

        if (isset($scanResult['file_scanner_result']) && strtoupper($scanResult['file_scanner_result'] == 'PASS')) {
            return true;
        }

        return false;
    }

    public function getScannerEndpoint()
    {
        return $this->scannerEndpoint;
    }
}
