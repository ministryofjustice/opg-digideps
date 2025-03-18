<?php

namespace App\Entity\Traits;

use App\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;

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

    public function setIdOfClientWithDetails($idOfClientWithDetails): self
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

    /**
     * @param int $numberOfReports
     *
     * @return HasReportTrait
     */
    public function setNumberOfReports($numberOfReports)
    {
        $this->numberOfReports = $numberOfReports;

        return $this;
    }
}
