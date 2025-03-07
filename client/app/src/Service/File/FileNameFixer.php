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

    public function lowerCaseFileExtension(UploadedFile $uploadedFile): UploadedFile
    {
        // lowercase file extension
        $originalFileExtension = $uploadedFile->getClientOriginalExtension();

        if ('' == $originalFileExtension) {
            return $uploadedFile;
        }

        $lowerCaseFileExtension = strtolower($originalFileExtension);

        if ($originalFileExtension === $lowerCaseFileExtension) {
            return $uploadedFile;
        }

        // get temporary path of current uploaded file
        $tempFileLocation = $uploadedFile->getRealPath();

        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $updatedFileName = $originalName.'.'.$lowerCaseFileExtension;

        // copy file to temporary location with corrected file extension, this is the same path as the original file
        copy($uploadedFile->getPathname(), $tempFileLocation);

        // create new file object with corrected extension
        return new UploadedFile(
            $tempFileLocation,
            $updatedFileName,
            $uploadedFile->getMimeType()
        );
    }
}
