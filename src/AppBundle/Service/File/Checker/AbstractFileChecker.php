<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Types\UploadableFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AbstractFileChecker
 * A generic place to hold common methods for checking files
 *
 * @package AppBundle\Service\File\Checker
 */
class AbstractFileChecker
{
    /**
     * Any other checks that are not found by the virus scan go here.
     * Checks file extension.
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function checkFile(UploadableFileInterface $file)
    {
        if (!self::hasValidFileExtension($file)) {
            throw new RiskyFileException('Invalid file extension');
        }

        return $file;
    }

    /**
     * Has the file got a valid extension
     *
     * @param UploadableFileInterface $file
     *
     * @return bool
     */
    protected static function hasValidFileExtension(UploadableFileInterface $file)
    {
        $uploadedFile = $file->getUploadedFile();
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (!in_array($extension, self::getAcceptedExtensions()) ||
            $uploadedFile->guessExtension() !== $extension
        ) {
            return false;
        }

        return true;
    }

    /**
     * List of accepted file extensions
     *
     * @todo generate list from config / env variables
     *
     * @return array
     */
    public static function getAcceptedExtensions()
    {
        return ['pdf', 'jpg', 'jpeg', 'png', 'tiff'];
    }
}
