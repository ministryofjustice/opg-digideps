<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Types\UploadableFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfChecker extends AbstractFileChecker implements FileCheckerInterface
{
    /**
     * Any other specific checks for a file type can go here
     *
     * Checks file extension.
     *
     * @param UploadableFileInterface $file
     * @return bool
     */
    public function checkFile(UploadableFileInterface $fileToStore)
    {
        parent::checkFile($fileToStore);

        $scanResult = $fileToStore->getScanResult();

        if ($scanResult['av_scan_result'] !== 'SUCCESS') {
            throw new VirusFoundException('Found virus in file');
        }

        if ($scanResult['pdf_scan_result'] !== 'SUCCESS') {
            throw new RiskyFileException('Risky content found in file');
        }

        return (bool) $scanResult['file_scanner_result'] !== 'SUCCESS';
    }
}
