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

        // not clear why the below is called. fileCheckers already do the job

        
//        $scanResult = $this->getScanResult();

        // not clear why this is done again here.

//        if ($scanResult['file_scanner_code'] !== 'PASS') {
//            throw new VirusFoundException('Found virus in file');
//        }
//
//        if ($scanResult['pdf_scan_result'] !== 'PASS') {
//            throw new RiskyFileException('Risky content found in file');
//        }
    }
}

