<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use AppBundle\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ExecutionContextInterface;
use AppBundle\Service\File\Types\UploadableFile;
/**
 * @Assert\Callback(methods={"isFileNameUnique"}, groups={"document"})
 */
class Document
{
    const FILE_NAME_MAX_LENGTH = 255;

    use CreationAudit;
    use HasReportTrait;

    /**
     * @param ExecutionContextInterface $context
     */
    public function isFileNameUnique(ExecutionContextInterface $context)
    {
        if (!($this->getFile() instanceof UploadableFile)) {
            return false;
        }

        $fileNames = [];
        foreach ($this->getReport()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }

        $fileOriginalName = $this->getFile()->getClientOriginalName();
        if (strlen($fileOriginalName) > self::FILE_NAME_MAX_LENGTH) {
            $context->addViolationAt('file', 'document.file.errors.maxMessage');
            return false;
        }

        if (in_array($fileOriginalName, $fileNames)) {
            $context->addViolationAt('file', 'document.file.errors.alreadyPresent');
            return false;
        }

        return true;

    }

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"document"})
     */
    private $id;

    /**
     * // add more validators here if needed
     * http://symfony.com/doc/current/reference/constraints/File.html
     *
     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
     * @Assert\File(
     *     maxSize = "15M",
     *     maxSizeMessage = "document.file.errors.maxSizeMessage",
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/jpg", "image/jpeg", "image/png"},
     *     mimeTypesMessage = "document.file.errors.mimeTypesMessage",
     *     groups={"document"}
     * )
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Document
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return Document
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
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
     * @return Document
     */
    public function setStorageReference($storageReference)
    {
        $this->storageReference = $storageReference;

        return $this;
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
