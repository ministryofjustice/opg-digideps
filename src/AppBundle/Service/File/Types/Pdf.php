<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;

class Pdf extends UploadableFile
{

    public function isSafe()
    {
        $scanResult = $this->getScanResult();

        $this->logger->warning('Confirming file is safe... ' . $this->getUploadedFile()->getClientOriginalName() .
            ' - ' . $this->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($scanResult));

        if (isset($scanResult['av_scan_result']) &&
            strtoupper($scanResult['av_scan_result'] == 'PASS')
            && strtoupper($scanResult['pdf_scan_result'] == 'PASS')
        ) {
            return true;
        }

        return false;
    }
}

