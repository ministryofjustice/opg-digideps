<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfChecker extends AbstractFileChecker implements FileCheckerInterface
{
    /**
     * Any other checks that are not found by the virus scan go here.
     * Checks file extension.
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function checkFile(UploadedFile $file)
    {
        if (!self::hasValidFileExtensiobn($file)) {
            throw new RiskyFileException('Invalid file extension');
        }

        return true;
    }


}
