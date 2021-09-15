<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use DateTime;
use JMS\Serializer\Annotation as JMS;

class ClientBenefitsCheck
{
    use HasReportTrait;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private string $id;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private DateTime $created;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report", "client-benefits-check"})
     */
    private string $whenLastCheckedEntitlement = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): ClientBenefitsCheck
    {
        $this->id = $id;

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): ClientBenefitsCheck
    {
        $this->created = $created;

        return $this;
    }

    public function getWhenLastCheckedEntitlement(): string
    {
        return $this->whenLastCheckedEntitlement;
    }

    public function setWhenLastCheckedEntitlement(string $whenLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->whenLastCheckedEntitlement = $whenLastCheckedEntitlement;

        return $this;
    }
}
