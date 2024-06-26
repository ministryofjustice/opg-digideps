<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameFixer extends FileUtility
{
    public function removeWhiteSpaceBeforeFileExtension(string $fileName): array|string|null
    {
        $pattern = "/\s+(\.[^.]+)$/";
        $replacement = '$1';

        return preg_replace($pattern, $replacement, $fileName);
    }

    public function addMissingFileExtension(UploadedFile $uploadedFile): string
    {
        if (empty($uploadedFile->getClientOriginalExtension())) {
            /** @var string $body */
            $body = file_get_contents($uploadedFile->getPathname());
            $mimeType = $this->mimeTypeDetector->detectMimeType($uploadedFile->getPathName(), $body);
            $fileExtension = $this->mimeToExtension($mimeType);

            return sprintf('%s.%s', $uploadedFile->getClientOriginalName(), $fileExtension);
        }

        return $uploadedFile->getClientOriginalName();
    }

    public function removeUnusualCharacters(string $fileName): array|string|null
    {
        $fileNameSpacesToUnderscores = str_replace(' ', '_', $fileName);
        $specialCharsRemoved = preg_replace('/[^A-Za-z0-9_.]/', '', $fileNameSpacesToUnderscores);

        return preg_replace('/[.](?=.*[.])/', '_', $specialCharsRemoved);
    }
}
