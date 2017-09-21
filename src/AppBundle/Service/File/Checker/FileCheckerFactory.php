<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\FileCheckerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileCheckerFactory
{

    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return FileCheckerInterface
     */
    public function factory(UploadedFile $uploadedFile)
    {
        switch ($uploadedFile->getMimeType())
        {
            case 'application/pdf':
                return $this->container->get('file_pdf')->setUploadedFile($uploadedFile);
            case "image/png":
                return $this->container->get('file_png')->setUploadedFile($uploadedFile);
            case "image/jpeg":
                return $this->container->get('file_jpg')->setUploadedFile($uploadedFile);
            default:
                throw new \RuntimeException("File type not supported");

        }
    }
}
