<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Document
{
    use CreationAudit;


    /**
     * // add more validators here if needed
     * http://symfony.com/doc/current/reference/constraints/File.html
     *
     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
     * @Assert\File(
     *     maxSize = "15M",
     *     maxSizeMessage = "document.fileName.maxSizeMessage",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "document.fileName.mimeTypesMessage",
     *     groups={"document"}
     * )
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $fileName;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @var string
     */
    private $storageReference;

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getStorageReference()
    {
        return $this->storageReference;
    }

    /**
     * @param string $storageReference
     */
    public function setStorageReference($storageReference)
    {
        $this->storageReference = $storageReference;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

}
