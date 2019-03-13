<?php

namespace AppBundle\v2\Assembler\Report;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\v2\Assembler\StatusAssembler;
use AppBundle\v2\DTO\StatusDto;

class FullReportAssembler implements ReportAssemblerInterface
{
    /** @var ReportAssemblerInterface  */
    private $reportSummaryAssembler;

    /** @var StatusAssembler  */
    private $statusDtoAssembler;

    /** @var ReportRepository */
    private $reportRepository;

    /**
     * @param ReportAssemblerInterface $reportSummaryAssembler
     * @param StatusAssembler $statusDtoAssembler
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        ReportAssemblerInterface $reportSummaryAssembler,
        StatusAssembler $statusDtoAssembler,
        ReportRepository $reportRepository
    ) {
        $this->reportSummaryAssembler = $reportSummaryAssembler;
        $this->statusDtoAssembler = $statusDtoAssembler;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param array $data
     * @return \AppBundle\v2\DTO\ReportDto
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

        $reportDto->setStatus( $this->assembleReportStatus($reportEntity));
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
