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
        if (!self::hasValidFileExtensiobn($file)) {
            throw new RiskyFileException('Invalid file extension');
        }

        return true;
    }

    /**
     * Has the file got a valid extension
     *
     * @param UploadableFileInterface $file
     *
     * @return bool
     */
    public static function hasValidFileExtensiobn(UploadableFileInterface $file)
    {
        $uploadedFile = $file->getUploadedFile();
        if (!in_array($uploadedFile->getClientOriginalExtension(), self::getAcceptedExtensions()) ||
            $uploadedFile->guessExtension() !== $uploadedFile->getClientOriginalExtension()
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
