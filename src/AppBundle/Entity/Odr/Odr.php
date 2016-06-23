<?php

namespace AppBundle\Entity\Odr;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

class Odr
{

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;


    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     */
    private $submitDate;

    /**
     * @JMS\Type("AppBundle\Entity\Client")
     * @var Client
     */
    private $client;

    /**
     * @JMS\Type("AppBundle\Entity\Odr\VisitsCare")
     *
     * @var VisitsCare
     */
    private $visitsCare;


    /**
     * @JMS\Type("boolean")
     * @var bool
     */
    private $submitted;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @param \DateTime $submitDate
     */
    public function setSubmitDate($submitDate)
    {
        $this->submitDate = $submitDate;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return VisitsCare
     */
    public function getVisitsCare()
    {
        return $this->visitsCare;
    }

    /**
     * @param VisitsCare $visitsCare
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;
    }



    /**
     * @return boolean
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param boolean $submitted
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;
    }

    public function isDue()
    {
        return false;
    }


}
