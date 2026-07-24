<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\MoneyReceivedOnClientsBehalfInterface;
use OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck as CustomAssert;
use JMS\Serializer\Annotation as JMS;

class MoneyReceivedOnClientsBehalf implements MoneyReceivedOnClientsBehalfInterface
{
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $id = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?\DateTime $created = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?ClientBenefitsCheck $clientBenefitsCheck = null;

    /**
     *
     * @CustomAssert\MoneyReceivedOnClientsBehalf(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $moneyType = null;

    /**
     *
     * @CustomAssert\MoneyReceivedOnClientsBehalf(groups={"client-benefits-check"})
     */
    #[JMS\Type('float')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?float $amount = null;

    /**
     *
     * @CustomAssert\MoneyReceivedOnClientsBehalf(groups={"client-benefits-check"})
     *
     * This will not be persisted - it exists to enable a checkbox in the form
     */
    #[JMS\Type('bool')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?bool $amountDontKnow = null;

    /**
     *
     * @CustomAssert\MoneyReceivedOnClientsBehalf(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $whoReceivedMoney;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): MoneyReceivedOnClientsBehalf
    {
        $this->id = $id;

        return $this;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(?\DateTime $created): MoneyReceivedOnClientsBehalf
    {
        $this->created = $created;

        return $this;
    }

    public function getClientBenefitsCheck(): ?ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(?ClientBenefitsCheck $clientBenefitsCheck): MoneyReceivedOnClientsBehalf
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getAmountDontKnow(): ?bool
    {
        return is_null($this->getAmount()) && !is_null($this->getMoneyType());
    }

    public function setAmountDontKnow(?bool $amountDontKnow): MoneyReceivedOnClientsBehalf
    {
        $this->amountDontKnow = $amountDontKnow;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): MoneyReceivedOnClientsBehalf
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMoneyType(): ?string
    {
        return $this->moneyType;
    }

    public function setMoneyType(?string $moneyType): MoneyReceivedOnClientsBehalf
    {
        $this->moneyType = $moneyType;

        return $this;
    }

    public function getWhoReceivedMoney(): ?string
    {
        return $this->whoReceivedMoney;
    }

    public function setWhoReceivedMoney(?string $whoReceivedMoney): MoneyReceivedOnClientsBehalf
    {
        $this->whoReceivedMoney = $whoReceivedMoney;

        return $this;
    }
}
