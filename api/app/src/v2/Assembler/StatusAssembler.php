<?php

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\ReportStatusServiceFactory;
use OPG\Digideps\Backend\v2\DTO\StatusDto;

class StatusAssembler
{
    public function __construct(private readonly ReportStatusServiceFactory $statusServiceFactory)
    {
    }

    /**
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
