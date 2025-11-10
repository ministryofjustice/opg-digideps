<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameManipulation extends FileUtility
{
    public function addMissingFileExtension(UploadedFile $uploadedFile): string
    {
        if (empty($uploadedFile->getClientOriginalExtension())) {
            /** @var string $body */
            $body = file_get_contents($uploadedFile->getPathname());
            $mimeType = $this->mimeTypeDetector->detectMimeType($uploadedFile->getPathName(), $body);
            $fileExtension = $this->mimeToExtension($mimeType);

            return sprintf('%s.%s', $uploadedFile->getClientOriginalName(), $fileExtension); /* @phpstan-ignore-line */
        }

        return $uploadedFile->getClientOriginalName();
    }

    public static function fileNameSanitation(string $fileName): string
    {
        $fileNameSplit = pathinfo($fileName);
        $fileName = $fileNameSplit['filename'];

        $endSpaces = preg_replace('/\s+(\.[^.]+)$/', '$1', $fileName);
        $remainingSpaces = preg_replace('/[[:blank:]]/', '_', $endSpaces); /* @phpstan-ignore-line */
        $specialChars = preg_replace('/[^\w_.-]/', '', $remainingSpaces); /* @phpstan-ignore-line */
        $hyphensAndPeriods = preg_replace('/([.-])/', '_', $specialChars) ?? ''; /* @phpstan-ignore-line */

        return  isset($fileNameSplit['extension']) ?
            $hyphensAndPeriods . '.' . $fileNameSplit['extension'] :
            $hyphensAndPeriods;
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
        $updatedFileName = $originalName . '.' . $lowerCaseFileExtension;

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
