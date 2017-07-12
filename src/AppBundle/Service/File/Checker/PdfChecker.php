<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfChecker implements FileCheckerInterface
{
    public function checkFile(UploadedFile $file)
    {
        throw new RiskyFileException('Found embedded JS in PDF');
    }
}
