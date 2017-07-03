<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Traits as ReportTraits;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

class Document
{
    const TYPE_PDF = 'pdf';
    const TYPE_JPG = 'jpg';


    /**
     * @var string
     */
    private $fileName;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $type;

    /**
     * Document constructor.
     * @param $fileName
     * @param $createdAt
     * @param $type
     */
    public function __construct($fileName, $createdAt, $type)
    {
        $this->fileName = $fileName;
        $this->createdAt = $createdAt;
        $this->type = $type;
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
    public function getType()
    {
        return $this->type;
    }

}
