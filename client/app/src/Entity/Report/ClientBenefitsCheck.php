<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\ClientBenefitsCheckInterface;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
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
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $whenLastCheckedEntitlement = null;

    /**
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?\DateTime $dateLastCheckedEntitlement = null;

    /**
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $neverCheckedExplanation = null;

    /**
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $doOthersReceiveMoneyOnClientsBehalf = '';

    /**
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    private ?string $dontKnowMoneyExplanation = null;

    /**
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Frontend\Entity\Report\MoneyReceivedOnClientsBehalf>')]
    #[JMS\Groups(['report', 'client-benefits-check'])]
    #[Assert\Valid(groups: ['client-benefits-check'])]
    private ?ArrayCollection $typesOfMoneyReceivedOnClientsBehalf = null;

    public function getWhenLastCheckedEntitlement(): ?string
    {
        return $this->whenLastCheckedEntitlement;
    }

    public function setWhenLastCheckedEntitlement(?string $whenLastCheckedEntitlement): ClientBenefitsCheck
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

    public function getDateLastCheckedEntitlement(): ?\DateTime
    {
        return $this->dateLastCheckedEntitlement;
    }

    public function setDateLastCheckedEntitlement(?\DateTime $dateLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->dateLastCheckedEntitlement = $dateLastCheckedEntitlement;

        return $this;
    }

    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    public function setCreated(?\DateTime $created): ClientBenefitsCheck
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

    /**
     * @return ArrayCollection<MoneyReceivedOnClientsBehalf>|null
     */
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
}
