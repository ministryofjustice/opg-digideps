<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasBankAccountTrait;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Gift
{
    use HasReportTrait;
    use HasBankAccountTrait;

    /**
     *
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['gift'])]
    private $id;

    /**
     * @var string
     *
     *
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gift'])]
    #[Assert\NotBlank(message: 'gifts.explanation.notBlank', groups: ['gift'])]
    private $explanation;

    /**
     * @var float
     *
     *
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gift'])]
    #[Assert\NotBlank(message: 'gifts.amount.notBlank', groups: ['gift'])]
    #[Assert\Type(type: 'numeric', message: 'gifts.amount.type', groups: ['gift'])]
    #[Assert\Range(min: 0.01, max: 100000000000, notInRangeMessage: 'gifts.amount.notInRangeMessage', groups: ['gift'])]
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
    public function setId($id): void
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
     */
    public function setExplanation($explanation): static
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
     */
    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
