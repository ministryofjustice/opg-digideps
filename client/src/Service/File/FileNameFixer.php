<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameFixer extends FileUtility
{
    /**
     * @return string|string[]|null
     */
    public function removeWhiteSpaceBeforeFileExtension(string $fileName)
    {
        $pattern = "/\s+(\.[^.]+)$/";
        $replacement = '$1';

        return preg_replace($pattern, $replacement, $fileName);
    }

    /**
     * @param string $relativeFilePath
     */
    public function addMissingFileExtension(UploadedFile $uploadedFile, string $fileBody): string
    {
        if (empty($uploadedFile->getClientOriginalExtension())) {
            $mimeType = $this->mimeTypeDetector->detectMimeType($uploadedFile->getPathName(), $fileBody);
            $fileExtension = $this->mimeToExtension($mimeType);

            return sprintf('%s.%s', $uploadedFile->getClientOriginalName(), $fileExtension);
        }

        return $uploadedFile->getClientOriginalName();
    }

    public function removeUnusualCharacters(string $fileName): array | string | null
    {
        $fileNameSpacesToUnderscores = str_replace(' ', '_', $fileName);
        $specialCharsRemoved = preg_replace('/[^A-Za-z0-9_.]/', '', $fileNameSpacesToUnderscores);

        return preg_replace('/[.](?=.*[.])/', '_', $specialCharsRemoved);
    }
}
