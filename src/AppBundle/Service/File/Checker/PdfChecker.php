<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;

class PdfChecker implements FileCheckerInterface
{
    public function checkFile($body)
    {
        throw new RiskyFileException('Found embedded JS in PDF');
    }
}
