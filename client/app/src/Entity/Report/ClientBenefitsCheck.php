<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\ClientBenefitsCheckInterface;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck as CustomAssert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ClientBenefitsCheck implements ClientBenefitsCheckInterface
{
    use HasReportTrait;

    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $id = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?\DateTime $created = null;

    /**
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $whenLastCheckedEntitlement = null;

    /**
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?\DateTime $dateLastCheckedEntitlement = null;

    /**
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $neverCheckedExplanation = null;

    /**
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $doOthersReceiveMoneyOnClientsBehalf = '';

    /**
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $dontKnowMoneyExplanation = null;

    /**
     * @var MoneyReceivedOnClientsBehalf[]|null
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyReceivedOnClientsBehalf>')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    #[Assert\Valid(groups: ['client-benefits-check'])]
    private ?array $typesOfMoneyReceivedOnClientsBehalf = null;

    public function getWhenLastCheckedEntitlement(): ?string
    {
        return $this->whenLastCheckedEntitlement;
    }

    public function setWhenLastCheckedEntitlement(?string $whenLastCheckedEntitlement): static
    {
        $this->whenLastCheckedEntitlement = $whenLastCheckedEntitlement;

        return $this;
    }

    public function getNeverCheckedExplanation(): ?string
    {
        return $this->neverCheckedExplanation;
    }

    public function setNeverCheckedExplanation(?string $neverCheckedExplanation): static
    {
        $this->neverCheckedExplanation = $neverCheckedExplanation;

        return $this;
    }

    public function getDateLastCheckedEntitlement(): ?\DateTime
    {
        return $this->dateLastCheckedEntitlement;
    }

    public function setDateLastCheckedEntitlement(?\DateTime $dateLastCheckedEntitlement): static
    {
        $this->dateLastCheckedEntitlement = $dateLastCheckedEntitlement;

        return $this;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(?\DateTime $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDoOthersReceiveMoneyOnClientsBehalf(): ?string
    {
        return $this->doOthersReceiveMoneyOnClientsBehalf;
    }

    public function setDoOthersReceiveMoneyOnClientsBehalf(?string $doOthersReceiveMoneyOnClientsBehalf): static
    {
        $this->doOthersReceiveMoneyOnClientsBehalf = $doOthersReceiveMoneyOnClientsBehalf;

        return $this;
    }

    public function getDontKnowMoneyExplanation(): ?string
    {
        return $this->dontKnowMoneyExplanation;
    }

    public function setDontKnowMoneyExplanation(?string $dontKnowMoneyExplanation): static
    {
        $this->dontKnowMoneyExplanation = $dontKnowMoneyExplanation;

        return $this;
    }

    /**
     * @return MoneyReceivedOnClientsBehalf[]|null
     */
    public function getTypesOfMoneyReceivedOnClientsBehalf(): ?array
    {
        return $this->typesOfMoneyReceivedOnClientsBehalf;
    }

    /**
     * @param MoneyReceivedOnClientsBehalf[]|null $typesOfMoneyReceivedOnClientsBehalf
     */
    public function setTypesOfMoneyReceivedOnClientsBehalf(?array $typesOfMoneyReceivedOnClientsBehalf): static
    {
        $this->typesOfMoneyReceivedOnClientsBehalf = $typesOfMoneyReceivedOnClientsBehalf;

        return $this;
    }

    public function addTypeOfMoneyReceivedOnClientsBehalf(MoneyReceivedOnClientsBehalf $moneyReceivedOnClientsBehalf): static
    {
        if (!is_null($this->typesOfMoneyReceivedOnClientsBehalf)) {
            $this->typesOfMoneyReceivedOnClientsBehalf[] = $moneyReceivedOnClientsBehalf;
        }

        return $this;
    }
}
