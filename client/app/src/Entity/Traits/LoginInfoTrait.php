<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;

trait LoginInfoTrait
{
    /**
     * @JMS\Type("integer")
     *
     * @var int|null
     */
    private $idOfClientWithDetails;

    /**
     * @JMS\Type("integer")
     *
     * @var int|null
     */
    private $activeReportId;

    /**
     * @JMS\Type("integer")
     *
     * @var int|null
     */
    private $numberOfReports;

    public function getIdOfClientWithDetails(): ?int
    {
        return $this->idOfClientWithDetails;
    }

    public function setIdOfClientWithDetails(int $idOfClientWithDetails): static
    {
        $this->idOfClientWithDetails = $idOfClientWithDetails;

        return $this;
    }

    /**
     * @return int
     */
    public function getActiveReportId()
    {
        return $this->activeReportId;
    }

    /**
     * @param int $activeReportId
     *
     * @return HasReportTrait
     */
    public function setActiveReportId($activeReportId)
    {
        $this->activeReportId = $activeReportId;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfReports()
    {
        return $this->numberOfReports;
    }

    public function setNumberOfReports(int $numberOfReports): static
    {
        $this->numberOfReports = $numberOfReports;

        return $this;
    }
}
