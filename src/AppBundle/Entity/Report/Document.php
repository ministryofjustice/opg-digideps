<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits as ReportTraits;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Document
{
    const TYPE_PDF = 'pdf';
    const TYPE_JPG = 'jpg';


    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
     * @Assert\File(mimeTypes={ "application/pdf" }, groups={"document"})
     *
     * @var string
     */
    private $fileName;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @var string
     */
    private $storageReference;

    /**
     * Document constructor.
     * @param $fileName
     * @param $createdAt
     * @param $storageReference
     */
    public function __construct($storageReference, $fileName, $createdAt)
    {
        $this->storageReference = $storageReference;
        $this->fileName = $fileName;
        $this->createdAt = $createdAt;
    }

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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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

}
