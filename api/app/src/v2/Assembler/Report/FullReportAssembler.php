<?php

namespace App\v2\Assembler\Report;

use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use App\v2\Assembler\StatusAssembler;
use App\v2\DTO\StatusDto;

class FullReportAssembler implements ReportAssemblerInterface
{
    public function __construct(
        private readonly ReportSummaryAssembler $reportSummaryAssembler,
        private readonly StatusAssembler $statusDtoAssembler,
        private readonly ReportRepository $reportRepository
    ) {
    }

    /**
     * @return \App\v2\DTO\ReportDto
     */
    public function assembleFromArray(array $data)
    {
        $reportDto = $this->reportSummaryAssembler->assembleFromArray($data);

        if (null === $reportDto->getId()) {
            return $reportDto;
        }

        if (null === ($reportEntity = $this->reportRepository->find($reportDto->getId()))) {
            return $reportDto;
        }

        $reportDto->setStatus($this->assembleReportStatus($reportEntity));
        $reportDto->setAvailableSections($reportEntity->getAvailableSections());

        return $reportDto;
    }

    /**
     * @return StatusDto
     */
    public function assembleReportStatus(Report $report)
    {
        return $this->statusDtoAssembler->assembleFromReport($report);
    }
}
