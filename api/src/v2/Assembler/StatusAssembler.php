<?php

namespace App\v2\Assembler;

use App\Entity\Report\Report;
use App\Service\ParameterStoreService;
use App\Service\ReportStatusServiceFactory;
use App\v2\DTO\StatusDto;
use DateTime;

class StatusAssembler
{
    /** @var ReportStatusServiceFactory */
    private $statusServiceFactory;
//    private ParameterStoreService $parameterStoreService;

//    /**
//     * @param ReportStatusServiceFactory $statusServiceFactory
//     */
//    public function __construct(ReportStatusServiceFactory $statusServiceFactory, ParameterStoreService $parameterStoreService)
//    {
//        $this->statusServiceFactory = $statusServiceFactory;
//        $this->parameterStoreService = $parameterStoreService;
//    }

    public function __construct(ReportStatusServiceFactory $statusServiceFactory)
    {
        $this->statusServiceFactory = $statusServiceFactory;
    }

    /**
     * @return StatusDto
     */
    public function assembleFromReport(Report $report)
    {
//        $featureLaunchDate = new DateTime($this->parameterStoreService->getFeatureFlag(ParameterStoreService::FLAG_BENEFITS_QUESTIONS));
//        $clientBenefitsSectionRequired = $report->requiresBenefitsCheckSection($featureLaunchDate);

        $excludeSections = [];

//        if (!$clientBenefitsSectionRequired) {
//            $excludeSections[] = Report::SECTION_CLIENT_BENEFITS_CHECK;
//        }

        $statusService = $this->statusServiceFactory->create($report, $excludeSections);

        $dto = new StatusDto();
        $dto->setStatus($statusService->getStatus());

        return $dto;
    }
}
