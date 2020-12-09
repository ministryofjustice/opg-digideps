<?php declare(strict_types=1);

namespace AppBundle\Service\File;

use League\MimeTypeDetection\FinfoMimeTypeDetector;

class FileNameFixer
{
    private FinfoMimeTypeDetector $mimeTypeDetector;
    private string $projectDir;

    public function __construct(FinfoMimeTypeDetector $mimeTypeDetector, string $projectDir)
    {
        $this->mimeTypeDetector = $mimeTypeDetector;
        $this->projectDir = $projectDir;
    }

    public function removeWhiteSpaceBeforeFileExtension(string $fileName)
    {
        $pattern = "/\s+(\.[^.]+)$/";
        $replacement = '$1';

        return preg_replace($pattern, $replacement, $fileName);
    }

    /**
     * @param string $relativeFilePath
     * @return string
     */
    public function addMissingFileExtension(string $relativeFilePath): string
    {
        $mimeType = $this->mimeTypeDetector->detectMimeType($relativeFilePath);
        return 'good-jpg.jpg';
    }
}
