<?php

namespace AppBundle\v2\Assembler;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportStatusServiceFactory;
use AppBundle\v2\DTO\StatusDto;

class StatusAssembler
{
    /** @var ReportStatusServiceFactory */
    private $statusServiceFactory;

    /**
     * @param ReportStatusServiceFactory $statusServiceFactory
     */
    public function __construct(ReportStatusServiceFactory $statusServiceFactory)
    {
        $this->statusServiceFactory = $statusServiceFactory;
    }

    /**
     * @param Report $report
     * @return StatusDto
     */
    public function assembleFromReport(Report $report)
    {
        $statusService = $this->statusServiceFactory->create($report);

        $dto = new StatusDto();
        $dto->setStatus($statusService->getStatus());

        return $dto;
    }
}
