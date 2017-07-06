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
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $createdOn;

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
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
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
