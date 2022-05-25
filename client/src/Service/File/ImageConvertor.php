<?php

declare(strict_types=1);

namespace App\Service\File;

use Exception;
use InvalidArgumentException;
use Orbitale\Component\ImageMagick\Command;

class ImageConvertor
{
    public const JFIF = 'jfif';
    public const HEIC = 'heic';
    public const SUPPORTED_IMAGE_TYPES = [self::JFIF, self::HEIC];

    /**
     * If a supported original file type id provided returns the body and filename of the newly converted file.
     * Unsupported file types will return the original body and filename of the original file.
     */
    public function convert(string $filePath, string $currentFileLocation)
    {
        $pathInfo = pathinfo($filePath);
        $directory = '/tmp';
        $extension = $pathInfo['extension'];
        $filename = $pathInfo['filename'];

        $fileType = $this->convertsTo($extension);

        if (is_null($fileType)) {
            return [file_get_contents($currentFileLocation), $filePath];
        }

        $targetExtension = $fileType->convertsTo();

        $imageMagick = new Command();
        $newPath = sprintf('%s/%s.%s', $directory, $filename, $targetExtension);
        $newFilename = sprintf('%s.%s', $filename, $targetExtension);
        $response = $imageMagick
            ->convert($currentFileLocation)
            ->output($newPath)
            ->run();

        // Check if the command failed and get the error if needed
        if ($response->hasFailed()) {
            throw new Exception('An error occurred: '.$response->getError());
        }

        $newBody = file_get_contents($newPath);

        // remove the created file from the filesystem - we're just interested in the body
        unlink(realpath($newPath));

        return [$newBody, $newFilename];
    }

    public function convertsTo(string $fileExtension): string
    {
        if (!in_array($fileExtension, self::SUPPORTED_IMAGE_TYPES)) {
            $message = sprintf('Image type "%s" is not supported. Supported image types are %s', $fileExtension, implode(', ', self::SUPPORTED_IMAGE_TYPES));
            throw new InvalidArgumentException($message);
        }

        return match ($fileExtension) {
            self::JFIF, self::HEIC => 'jpeg',
        };
    }
}
