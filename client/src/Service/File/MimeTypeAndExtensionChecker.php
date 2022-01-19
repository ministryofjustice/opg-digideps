<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimeTypeAndExtensionChecker extends FileUtility
{
    public function check(UploadedFile $uploadedFile, string $fileBody): bool
    {
        $fileExtension = $uploadedFile->getClientOriginalExtension();

        if ('jpg' === $fileExtension) {
            $fileExtension = 'jpeg';
        }

        $mimeType = $this->mimeTypeDetector->detectMimeType($uploadedFile->getPathName(), $fileBody);

        return $this->mimeMap[$mimeType] === $fileExtension;
    }
}
