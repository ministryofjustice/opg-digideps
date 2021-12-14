<?php

declare(strict_types=1);

namespace App\Entity\Ndr;

use App\Entity\IncomeReceivedOnClientsBehalfInterface;
use App\Validator\Constraints\ClientBenefitsCheck as CustomAssert;
use DateTime;
use JMS\Serializer\Annotation as JMS;

class IncomeReceivedOnClientsBehalf implements IncomeReceivedOnClientsBehalfInterface
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
     * @JMS\Type("App\Entity\Ndr\ClientBenefitsCheck")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?ClientBenefitsCheck $clientBenefitsCheck = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\IncomeReceivedOnClientsBehalf(groups={"client-benefits-check"})
     */
    private ?string $incomeType = null;

    /**
     * @JMS\Type("float")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\IncomeReceivedOnClientsBehalf(groups={"client-benefits-check"})
     */
    private ?float $amount = null;

    /**
     * @JMS\Type("bool")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\IncomeReceivedOnClientsBehalf(groups={"client-benefits-check"})
     *
     * This will not be persisted - it exists to enable a checkbox in the form
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

    public function getAmountDontKnow(): ?bool
    {
        return is_null($this->getAmount()) && !is_null($this->getIncomeType());
    }

    public function setAmountDontKnow(?bool $amountDontKnow): IncomeReceivedOnClientsBehalf
    {
        $this->amountDontKnow = $amountDontKnow;

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

    public function getIncomeType(): ?string
    {
        return $this->incomeType;
    }

    public function setIncomeType(?string $incomeType): IncomeReceivedOnClientsBehalf
    {
        $this->incomeType = $incomeType;

        return $this;
    }
}
