<?php

namespace App\v2\Assembler;

use App\Entity\Report\Report;
use App\Service\ReportStatusServiceFactory;
use App\v2\DTO\StatusDto;

class StatusAssembler
{
    /** @var ReportStatusServiceFactory */
    private $statusServiceFactory;

    public function __construct(ReportStatusServiceFactory $statusServiceFactory)
    {
        $this->statusServiceFactory = $statusServiceFactory;
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
