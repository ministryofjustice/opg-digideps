<?php

namespace App\v2\Assembler\Report;

use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use App\v2\Assembler\StatusAssembler;
use App\v2\DTO\StatusDto;

class FullReportAssembler implements ReportAssemblerInterface
{
    /** @var ReportSummaryAssembler  */
    private $reportSummaryAssembler;

    /** @var StatusAssembler  */
    private $statusDtoAssembler;

    /** @var ReportRepository */
    private $reportRepository;

    /**
     * @param ReportSummaryAssembler $reportSummaryAssembler
     * @param StatusAssembler $statusDtoAssembler
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        ReportSummaryAssembler $reportSummaryAssembler,
        StatusAssembler $statusDtoAssembler,
        ReportRepository $reportRepository
    ) {
        $this->reportSummaryAssembler = $reportSummaryAssembler;
        $this->statusDtoAssembler = $statusDtoAssembler;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param array $data
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
     * @param Report $report
     * @return StatusDto
     */
    public function assembleReportStatus(Report $report)
    {
        return $this->statusDtoAssembler->assembleFromReport($report);
    }
}
