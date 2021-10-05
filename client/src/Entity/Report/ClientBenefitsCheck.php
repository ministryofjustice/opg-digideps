<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use DateTime;
use JMS\Serializer\Annotation as JMS;

class ClientBenefitsCheck
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
     */
    private string $whenLastCheckedEntitlement = '';

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?DateTime $dateLastCheckedEntitlement = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private ?string $neverCheckedExplanation = null;

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
}
