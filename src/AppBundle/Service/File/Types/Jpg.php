<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;

class Jpg extends UploadableFile
{

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function checkFile()
    {
        parent::callFileCheckers();

        $scanResult = $this->getScanResult();

        if ($scanResult['av_scan_result'] !== 'PASS') {
            throw new VirusFoundException('Found virus in file');
        }

        if ($scanResult['pdf_scan_result'] !== 'PASS') {
            throw new RiskyFileException('Risky content found in file');
        }
    }
}

