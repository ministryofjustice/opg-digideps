<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfDeputyInterimCost
{
    /**
     * @var int
     */
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var string
     *
     *
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['profDeputyInterimCosts'])]
    #[Assert\Range(min: 0.01, max: 10000000, notInRangeMessage: 'profDeputyInterimCost.amount.notInRangeMessage', groups: ['prof-deputy-interim-costs'])]
    private $amount;

    /**
     * @var \DateTime
     *
     *
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['profDeputyInterimCosts'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'profDeputyInterimCost.date.notValid', groups: ['prof-deputy-interim-costs'])]
    #[Assert\LessThanOrEqual('today', message: 'profDeputyInterimCost.date.notFuture', groups: ['prof-deputy-interim-costs'])]
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
    public function setId($id): void
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
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }
}
