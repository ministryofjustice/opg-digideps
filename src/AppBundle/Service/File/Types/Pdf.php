<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;

class Pdf extends UploadableFile
{
    protected $scannerEndpoint = 'upload/pdf';

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function checkFile()
    {
        parent::callFileCheckers();
    }
}

