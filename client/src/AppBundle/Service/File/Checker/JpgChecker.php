<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\UploadableFileInterface;

class JpgChecker extends AbstractFileChecker implements FileCheckerInterface
{
    /**
     * Any other specific checks for a file type can go here
     *
     * Checks file extension.
     *
     * @param  UploadableFileInterface $file
     * @return bool
     */
    public function checkFile(UploadableFileInterface $fileToStore)
    {
        return parent::checkFile($fileToStore);
    }
}
