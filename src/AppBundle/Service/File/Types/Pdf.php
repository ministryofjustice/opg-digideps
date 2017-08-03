<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;

class Pdf extends UploadableFile
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

        if (strtoupper(trim($scanResult['file_scanner_result'])) !== 'PASS') {
            if ($scanResult['av_scan_result'] !== 'PASS') {
                $this->logger->warning('Virus found in ' . $file->getUploadedFile()->getClientOriginalName() .
                    ' - ' . $file->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($scanResult));
                throw new VirusFoundException('Found virus in file');
            }

            if ($scanResult['pdf_scan_result'] !== 'PASS') {
                $this->logger->warning('Risky content found in ' . $file->getUploadedFile()->getClientOriginalName() .
                    ' - ' . $file->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($scanResult));
                throw new RiskyFileException('Found virus in file');
            }

            $this->logger->warning('File scan failure for ' . $file->getUploadedFile()->getClientOriginalName() .
                ' - ' . $file->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($scanResult));

        } else {
            $this->logger->info('Scan results for: ' . $file->getUploadedFile()->getClientOriginalName() .
                ' - ' . $file->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($scanResult));
        }
    }
}

