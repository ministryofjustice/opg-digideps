<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\UploadableFileInterface;

interface FileCheckerInterface
{
    /**
     * @param UploadableFileInterface $file
     *
     * @return mixed
     */
    public function checkFile(UploadableFileInterface $file);
}
