<?php

namespace OPG\Digideps\Backend\v2\Assembler\Report;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\v2\Assembler\StatusAssembler;
use OPG\Digideps\Backend\v2\DTO\StatusDto;

class FullReportAssembler implements ReportAssemblerInterface
{
    public function __construct(
        private readonly ReportSummaryAssembler $reportSummaryAssembler,
        private readonly StatusAssembler $statusDtoAssembler,
        private readonly ReportRepository $reportRepository
    ) {
    }

    /**
     * @return \OPG\Digideps\Backend\v2\DTO\ReportDto
     */
    public function assembleFromArray(array $data)
    {
        $reportDto = $this->reportSummaryAssembler->assembleFromArray($data);

        if (null === $reportDto->getId()) {
            return $reportDto;
        }

        $reportEntity = $this->reportRepository->find($reportDto->getId());
        if (null === $reportEntity) {
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
