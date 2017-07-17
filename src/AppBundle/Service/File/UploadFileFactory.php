<?php

namespace AppBundle\Service\File;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileFactory
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
    public function createFileToStore(UploadedFile $uploadedFile)
    {
        switch ($uploadedFile->getMimeType())
        {
            case 'application/pdf':
                return $this->container->get('file_pdf')->setUploadedFile($uploadedFile);
                break;
            // more mime types to go here
        }
    }
}
