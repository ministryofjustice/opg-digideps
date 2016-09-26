<?php

namespace AppBundle\Entity\Odr;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Expense
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     *
     * @Assert\NotBlank(message="odr.expenses.explanation.notBlank", groups={"odr-expenses"})
     */
    private $explanation;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-expenses"})
     *
     * @Assert\NotBlank(message="odr.expenses.amount.notBlank", groups={"odr-expenses"})
     * @Assert\Type(type="numeric", message="odr.expenses.amount.type", groups={"odr-expenses"})
     *
     * @var string
     */
    private $amount;

    /**
     * @return mixed
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * @param mixed $explanation
     *
     * @return Expense
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
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
     *
     * @return Expense
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
