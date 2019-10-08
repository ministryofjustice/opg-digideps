<?php

namespace AppBundle\Service\File\Scanner;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScannerEndpointResolver
{
    const PDF_ENDPOINT = 'upload/pdf';
    const JPEG_ENDPOINT = 'upload/jpeg';
    const PNG_ENDPOINT = 'upload/png';

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function resolve(UploadedFile $file)
    {
        switch ($file->getMimeType()) {
            case 'application/pdf':
                return self::PDF_ENDPOINT;
            case 'image/png':
                return self::PNG_ENDPOINT;
            case 'image/jpeg':
                return self::JPEG_ENDPOINT;
            default:
                throw new \RuntimeException('File type not supported');
        }
    }
}
