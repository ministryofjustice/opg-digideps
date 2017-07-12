<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
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
     * Has the file got a valid extension
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public static function hasValidFileExtensiobn(UploadedFile $file)
    {
        if (!in_array($file->getClientOriginalExtension(), self::getAcceptedExtensions()) ||
            $file->guessExtension() !== $file->getClientOriginalExtension()
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
