<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Gift
{
    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"gift"})
     *
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"gift"})
     *
     * @Assert\NotBlank(message="gifts.explanation.notBlank", groups={"gift"})
     */
    private $explanation;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"gift"})
     *
     * @Assert\NotBlank(message="gifts.amount.notBlank", groups={"gift"})
     * @Assert\Type(type="numeric", message="gifts.amount.type", groups={"gift"})
     * @Assert\Range(min=0.01, max=10000000, minMessage = "gifts.amount.minMessage", maxMessage = "gifts.amount.maxMessage", groups={"gift"})
     *
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
     * @return Gift
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
     * @return Gift
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
