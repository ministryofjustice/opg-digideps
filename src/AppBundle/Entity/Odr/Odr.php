<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Client;
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
     * @JMS\Type("boolean")
     *  @JMS\Groups({"submit"})
     * @var bool
     */
    private $submitted;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"submit"})
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
     * @return Client
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

        return $this;
    }

    public function isDue()
    {
        return false;
    }

    /**
     * Return the due date (calculated as court order date + 40 days)
     *
     * @return \DateTime $dueDate
     */
    public function getDueDate()
    {
        $client = $this->getClient();
        if (!$client instanceof Client) {
            return;
        }

        $cod = $client->getCourtDate();

        if (!$cod instanceof \DateTime) {
            return;
        }
        $dueDate = clone $cod;
        $dueDate->modify('+40 days');

        return $dueDate;
    }


}
