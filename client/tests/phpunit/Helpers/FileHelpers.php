<?php declare(strict_types=1);


namespace DigidepsTests\Helpers;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHelpers extends KernelTestCase
{
    /**
     * Creates an UploadedFile object based on an existing file in the project.
     *
     * @param string $fileLocation
     * @param string $originalName
     * @param string $mimeType
     * @return UploadedFile
     */
    static public function generateUploadedFile(string $fileLocation, string $originalName, string $mimeType)
    {
        //@TODO drop the /../ file path when projectDir works as expected in Symfony 4+
        $projectDir = (self::bootKernel(['debug' => false]))->getProjectDir();
        $location = sprintf('%s/../%s', $projectDir, $fileLocation);

        return new UploadedFile($location, $originalName, $mimeType, null);
    }
}
