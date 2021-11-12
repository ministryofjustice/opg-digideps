<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\Report\Traits\HasReportTrait;
use App\Validator\Constraints\ClientBenefitsCheck as CustomAssert;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ClientBenefitsCheck implements ClientBenefitsCheckInterface
{
    use HasReportTrait;

    const WHEN_CHECKED_I_HAVE_CHECKED = 'haveChecked';
    const WHEN_CHECKED_IM_CURRENTLY_CHECKING = 'currentlyChecking';
    const WHEN_CHECKED_IVE_NEVER_CHECKED = 'neverChecked';

    const OTHER_INCOME_YES = 'yes';
    const OTHER_INCOME_NO = 'no';
    const OTHER_INCOME_DONT_KNOW = 'dontKnow';

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
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private string $whenLastCheckedEntitlement = '';

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?DateTime $dateLastCheckedEntitlement = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?string $neverCheckedExplanation = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?string $doOthersReceiveIncomeOnClientsBehalf = '';

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     */
    private ?string $dontKnowIncomeExplanation = null;

    /**
     * @JMS\Type("ArrayCollection<App\Entity\Report\IncomeReceivedOnClientsBehalf>")
     * @JMS\Groups({"report", "client-benefits-check"})
     *
     * @CustomAssert\ClientBenefitsCheck(groups={"client-benefits-check"})
     * @Assert\Valid(groups={"client-benefits-check"})
     */
    private ?ArrayCollection $typesOfIncomeReceivedOnClientsBehalf = null;

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

    public function getDoOthersReceiveIncomeOnClientsBehalf(): ?string
    {
        return $this->doOthersReceiveIncomeOnClientsBehalf;
    }

    public function setDoOthersReceiveIncomeOnClientsBehalf(?string $doOthersReceiveIncomeOnClientsBehalf): ClientBenefitsCheck
    {
        $this->doOthersReceiveIncomeOnClientsBehalf = $doOthersReceiveIncomeOnClientsBehalf;

        return $this;
    }

    public function getDontKnowIncomeExplanation(): ?string
    {
        return $this->dontKnowIncomeExplanation;
    }

    public function setDontKnowIncomeExplanation(?string $dontKnowIncomeExplanation): ClientBenefitsCheck
    {
        $this->dontKnowIncomeExplanation = $dontKnowIncomeExplanation;

        return $this;
    }

    public function getTypesOfIncomeReceivedOnClientsBehalf(): ?ArrayCollection
    {
        return $this->typesOfIncomeReceivedOnClientsBehalf;
    }

    public function setTypesOfIncomeReceivedOnClientsBehalf(?ArrayCollection $typesOfIncomeReceivedOnClientsBehalf): ClientBenefitsCheck
    {
        $this->typesOfIncomeReceivedOnClientsBehalf = $typesOfIncomeReceivedOnClientsBehalf;

        return $this;
    }

    public function addTypeOfIncomeReceivedOnClientsBehalf(IncomeReceivedOnClientsBehalf $incomeReceivedOnClientsBehalf): ClientBenefitsCheck
    {
        $this->typesOfIncomeReceivedOnClientsBehalf->add($incomeReceivedOnClientsBehalf);

        return $this;
    }
}
