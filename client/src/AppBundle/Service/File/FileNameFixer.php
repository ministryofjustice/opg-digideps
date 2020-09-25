<?php declare(strict_types=1);

namespace AppBundle\Service\File;

class FileNameFixer
{
    public static function removeWhiteSpaceBeforeFileExtension(string $fileName)
    {
        $pattern = "/\s+(\.[^.]+)$/";
        $replacement = '$1';

        return preg_replace($pattern, $replacement, $fileName);
    }
}
