<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameFixer extends FileUtility
{
    public static function removeWhiteSpaceBeforeFileExtension(string $fileName): string
    {
        return preg_replace('/\s+(\.[^.]+)$/', '$1', $fileName) ?? '';
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

    public static function removeUnusualCharacters(?string $fileName): string
    {
        $fileNameSpacesToUnderscores = preg_replace('[[[:blank:]]]', '_', $fileName ?? '');
        $specialCharsRemoved = preg_replace('/[^\w_.-]/', '', $fileNameSpacesToUnderscores ?? '');

        // Confirm if we have a file ext
        $regexPattern = preg_match('/\.\w+$/', $fileNameSpacesToUnderscores ?? '')?
            '/([.-])(?=.*\.)/' :
            '/([.-])/';

        return preg_replace($regexPattern, '_', $specialCharsRemoved) ?? '';
    }

    public static function lowerCaseFileExtension(UploadedFile $uploadedFile): UploadedFile
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
