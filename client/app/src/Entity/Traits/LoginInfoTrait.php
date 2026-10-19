<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use JMS\Serializer\Annotation as JMS;

trait LoginInfoTrait
{
    /**
     * @var int|null
     */
    #[JMS\Type('integer')]
    private ?int $idOfClientWithDetails = null;

    /**
     * @var int|null
     */
    #[JMS\Type('integer')]
    private ?int $activeReportId = null;

    /**
     * @var int|null
     */
    #[JMS\Type('integer')]
    private ?int $numberOfReports = null;

    public function getIdOfClientWithDetails(): ?int
    {
        return $this->idOfClientWithDetails;
    }

    public function setIdOfClientWithDetails(int $idOfClientWithDetails): static
    {
        $this->idOfClientWithDetails = $idOfClientWithDetails;

        return $this;
    }

    public function getActiveReportId(): ?int
    {
        return $this->activeReportId;
    }

    public function setActiveReportId(int $activeReportId): static
    {
        $this->activeReportId = $activeReportId;

        return $this;
    }

    public function getNumberOfReports(): ?int
    {
        return $this->numberOfReports;
    }

    public function setNumberOfReports(int $numberOfReports): static
    {
        $this->numberOfReports = $numberOfReports;

        return $this;
    }
}
