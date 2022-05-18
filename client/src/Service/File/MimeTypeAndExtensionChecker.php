<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimeTypeAndExtensionChecker extends FileUtility
{
    public function check(UploadedFile $uploadedFile, string $fileBody): bool
    {
        $fileExtension = $uploadedFile->getClientOriginalExtension();

        $mimeType = $this->mimeTypeDetector->detectMimeType($uploadedFile->getPathName(), $fileBody);

        if (is_array($this->mimeMap[$mimeType])) {
            $match = false;

            foreach ($this->mimeMap[$mimeType] as $extension) {
                $matched = $extension === $fileExtension;
                $match = $matched;
                if ($matched) {
                    break;
                }
            }

            return $match;
        }

        return $this->mimeMap[$mimeType] === $fileExtension;
    }
}
