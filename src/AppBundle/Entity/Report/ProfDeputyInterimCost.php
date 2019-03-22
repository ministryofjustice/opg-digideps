<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

class ProfDeputyInterimCost
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"profDeputyInterimCosts"})
     *
     * @Assert\Range(min=0.01, max=10000000, minMessage = "profDeputyInterimCost.amount.minMessage", maxMessage = "profDeputyInterimCost.amount.maxMessage", groups={"prof-deputy-interim-costs"})
     */
    private $amount;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"profDeputyInterimCosts"})
     *
     * @Assert\Date(message="profDeputyInterimCost.date.notValid", groups={"prof-deputy-interim-costs"})
     * @Assert\LessThanOrEqual("today", message="profDeputyInterimCost.date.notFuture", groups={"prof-deputy-interim-costs"})
     */
    private $date;


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
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }


    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }


}
