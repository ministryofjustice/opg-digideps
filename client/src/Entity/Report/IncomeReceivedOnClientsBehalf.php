<?php

declare(strict_types=1);

namespace App\Entity\Report;

use DateTime;
use JMS\Serializer\Annotation as JMS;

class IncomeReceivedOnClientsBehalf
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?string $id = null;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?DateTime $created = null;

    /**
     * @JMS\Type("App\Entity\Report\ClientBenefitsCheck")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?ClientBenefitsCheck $clientBenefitsCheck = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?string $incomeType = null;

    /**
     * @JMS\Type("float")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?float $amount = null;

    /**
     * THIS I BREAKING JMS - FIND A WAY TO HAVE TICK BOX WITHOUT BREAKING IT :(.
     *
     * @JMS\Exclude()
     *
     * @var bool|null
     *                This will never be sent to API - this property exists to enable a checkbox in the form
     */
    private ?bool $amountDontKnow = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): IncomeReceivedOnClientsBehalf
    {
        $this->id = $id;

        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(?DateTime $created): IncomeReceivedOnClientsBehalf
    {
        $this->created = $created;

        return $this;
    }

    public function getClientBenefitsCheck(): ?ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(?ClientBenefitsCheck $clientBenefitsCheck): IncomeReceivedOnClientsBehalf
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getIncomeType(): ?string
    {
        return $this->incomeType;
    }

    public function setIncomeType(?string $incomeType): IncomeReceivedOnClientsBehalf
    {
        $this->incomeType = $incomeType;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): IncomeReceivedOnClientsBehalf
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountDontKnow(): ?bool
    {
        return $this->amountDontKnow;
    }

    public function setAmountDontKnow(?bool $amountDontKnow): IncomeReceivedOnClientsBehalf
    {
        $this->amountDontKnow = $amountDontKnow;

        return $this;
    }
}
