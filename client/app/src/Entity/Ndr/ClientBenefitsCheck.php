<?php

declare(strict_types=1);

namespace App\Entity\Ndr;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\Ndr\Traits\HasNdrTrait;
use App\Validator\Constraints\ClientBenefitsCheck as CustomAssert;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ClientBenefitsCheck implements ClientBenefitsCheckInterface
{
    use HasNdrTrait;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     */
    private ?string $id = null;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     */
    private ?DateTime $created = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private string $whenLastCheckedEntitlement = '';

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?DateTime $dateLastCheckedEntitlement = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?string $neverCheckedExplanation = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?string $doOthersReceiveMoneyOnClientsBehalf = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?string $dontKnowMoneyExplanation = null;

    /**
     * @JMS\Type("ArrayCollection<App\Entity\Ndr\MoneyReceivedOnClientsBehalf>")
     * @JMS\Groups({"report", "ndr", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     * @Assert\Valid(groups={"client-benefits-check"})
     */
    private ?ArrayCollection $typesOfMoneyReceivedOnClientsBehalf = null;

    /**
     * @JMS\Type("App\Entity\Ndr\Ndr")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?Ndr $report = null;

    public function getWhenLastCheckedEntitlement(): string
    {
        return $this->whenLastCheckedEntitlement;
    }

    public function setWhenLastCheckedEntitlement(string $whenLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->whenLastCheckedEntitlement = $whenLastCheckedEntitlement;

        return $this;
    }

    public function getNeverCheckedExplanation(): ?string
    {
        return $this->neverCheckedExplanation;
    }

    public function setNeverCheckedExplanation(?string $neverCheckedExplanation): ClientBenefitsCheck
    {
        $this->neverCheckedExplanation = $neverCheckedExplanation;

        return $this;
    }

    public function getDateLastCheckedEntitlement(): ?DateTime
    {
        return $this->dateLastCheckedEntitlement;
    }

    public function setDateLastCheckedEntitlement(?DateTime $dateLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->dateLastCheckedEntitlement = $dateLastCheckedEntitlement;

        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(?DateTime $created): ClientBenefitsCheck
    {
        $this->created = $created;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): ClientBenefitsCheck
    {
        $this->id = $id;

        return $this;
    }

    public function getDoOthersReceiveMoneyOnClientsBehalf(): ?string
    {
        return $this->doOthersReceiveMoneyOnClientsBehalf;
    }

    public function setDoOthersReceiveMoneyOnClientsBehalf(?string $doOthersReceiveMoneyOnClientsBehalf): ClientBenefitsCheck
    {
        $this->doOthersReceiveMoneyOnClientsBehalf = $doOthersReceiveMoneyOnClientsBehalf;

        return $this;
    }

    public function getDontKnowMoneyExplanation(): ?string
    {
        return $this->dontKnowMoneyExplanation;
    }

    public function setDontKnowMoneyExplanation(?string $dontKnowMoneyExplanation): ClientBenefitsCheck
    {
        $this->dontKnowMoneyExplanation = $dontKnowMoneyExplanation;

        return $this;
    }

    public function getTypesOfMoneyReceivedOnClientsBehalf(): ?ArrayCollection
    {
        return $this->typesOfMoneyReceivedOnClientsBehalf;
    }

    public function setTypesOfMoneyReceivedOnClientsBehalf(?ArrayCollection $typesOfMoneyReceivedOnClientsBehalf): ClientBenefitsCheck
    {
        $this->typesOfMoneyReceivedOnClientsBehalf = $typesOfMoneyReceivedOnClientsBehalf;

        return $this;
    }

    public function addTypeOfMoneyReceivedOnClientsBehalf(MoneyReceivedOnClientsBehalf $moneyReceivedOnClientsBehalf): ClientBenefitsCheck
    {
        $this->typesOfMoneyReceivedOnClientsBehalf->add($moneyReceivedOnClientsBehalf);

        return $this;
    }

    public function getReport(): ?Ndr
    {
        return $this->report;
    }

    public function setReport(?Ndr $report): ClientBenefitsCheck
    {
        $this->report = $report;

        return $this;
    }
}
