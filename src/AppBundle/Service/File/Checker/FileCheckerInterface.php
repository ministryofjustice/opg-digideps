<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\UploadableFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileCheckerInterface
{
    /**
     * @param UploadableFileInterface $file
     *
     * @return mixed
     */
    public function checkFile(UploadableFileInterface $file);
}
