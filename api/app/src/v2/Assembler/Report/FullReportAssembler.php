<?php

namespace OPG\Digideps\Backend\v2\Assembler\Report;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\v2\Assembler\StatusAssembler;
use OPG\Digideps\Backend\v2\DTO\ReportDto;
use OPG\Digideps\Backend\v2\DTO\StatusDto;

class FullReportAssembler implements ReportAssemblerInterface
{
    public function __construct(
        private readonly ReportSummaryAssembler $reportSummaryAssembler,
        private readonly StatusAssembler $statusDtoAssembler,
        private readonly ReportRepository $reportRepository
    ) {
    }

    public function assembleFromArray(array $data): ReportDto
    {
        $reportDto = $this->reportSummaryAssembler->assembleFromArray($data);

        if ($reportDto->getId() === null) {
            return $reportDto;
        }

        $reportEntity = $this->reportRepository->find($reportDto->getId());
        if ($reportEntity === null) {
            return $reportDto;
        }

        $reportDto->setStatus($this->assembleReportStatus($reportEntity));
        $reportDto->setAvailableSections($reportEntity->getAvailableSections());

        return $reportDto;
    }

    public function assembleReportStatus(Report $report): StatusDto
    {
        return $this->statusDtoAssembler->assembleFromReport($report);
    }
}
