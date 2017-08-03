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
            case 'image/jpeg':
            case 'image/jpg':
                return $this->container->get('file_jpg')->setUploadedFile($uploadedFile);
            case 'image/png':
                return $this->container->get('file_png')->setUploadedFile($uploadedFile);
            case 'application/pdf':
            case 'application/x-pdf':
                return $this->container->get('file_pdf')->setUploadedFile($uploadedFile);
            // more mime types to go here
            default:
                $this->logger->warning('Unsupported File type -> ' . $thiw->getUploadeedFile()->getClientOriginalExtension());
        }
    }
}
