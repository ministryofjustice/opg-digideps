<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Expense
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"expense"})
     *
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expense"})
     *
     * @Assert\NotBlank(message="expenses.explanation.notBlank", groups={"deputy-expense"})
     */
    private $explanation;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"expense"})
     *
     * @Assert\NotBlank(message="expenses.amount.notBlank", groups={"deputy-expense"})
     * @Assert\Type(type="numeric", message="expenses.amount.type", groups={"deputy-expense"})
     * @Assert\Range(min=0.01, max=10000000, minMessage = "expenses.amount.minMessage", maxMessage = "expenses.amount.maxMessage", groups={"deputy-expense"})
     * @var string
     */
    private $amount;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


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
