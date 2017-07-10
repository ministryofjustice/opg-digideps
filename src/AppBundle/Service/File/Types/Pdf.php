<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\FileCheckerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Pdf
{
    /**
     * @var FileCheckerInterface[]
     */
    private $fileCheckers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UploadedFile $file
     */
    private $uploadedFile;

    /**
     * FileUploader constructor.
     */
    public function __construct(
        FileCheckerInterface $virusChecker,
        FileCheckerInterface $fileChecker,
        LoggerInterface $logger
    )
    {
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
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
}

