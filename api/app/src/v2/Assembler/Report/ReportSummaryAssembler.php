<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Assembler\Report;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\DTO\ReportDto;
use OPG\Digideps\Common\CourtOrder\CourtOrderDto;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Validating\ValidatingArray;

class ReportSummaryAssembler
{
    use DtoPropertySetterTrait;

    public function assembleFromArray(array $data): ReportDto
    {
        $dto = new ReportDto();

        // exclude court orders, as we will process these separately
        $this->setPropertiesFromData($dto, $data, ['courtOrders']);

        $courtOrders = [];

        /** @var array $courtOrderData */
        foreach ([$data['pfaCourtOrder'] ?? null, $data['hwCourtOrder'] ?? null] as $courtOrderData) {
            if ($courtOrderData === null) {
                continue;
            }

            $courtOrderDataValidated = new ValidatingArray($courtOrderData);

            $courtOrders[] = new CourtOrderDto(
                $courtOrderDataValidated->getIntegerOrThrow('id'),
                $courtOrderDataValidated->getStringOrThrow('courtOrderUid'),
                CourtOrderType::tryFrom($courtOrderDataValidated->getStringOrThrow('orderType')),
                CourtOrderReportType::tryFrom($courtOrderDataValidated->getStringOrThrow('orderReportType')),
                $courtOrderDataValidated->getStringOrThrow('status'),
                $courtOrderDataValidated->getObjectOrThrow('orderMadeDate', \DateTime::class)
            );
        }

        $dto->setCourtOrders($courtOrders);

        return $dto;
    }

    public function assembleFromEntity(Report $report): ReportDto
    {
        $dto = new ReportDto();

        $dto->setId($report->getId());
        $dto->setType("{$report->getReportType()}");
        $dto->setSubmitted($report->getSubmitted());
        $dto->setStartDate($report->getStartDate());
        $dto->setEndDate($report->getEndDate());
        $dto->setDueDate($report->getDueDate());
        $dto->setSubmitDate($report->getSubmitDate());
        $dto->setUnSubmitDate($report->getUnSubmitDate());

        $courtOrders = [];
        foreach ($report->getCourtOrders() as $courtOrder) {
            $courtOrders[] = new CourtOrderDto(
                $courtOrder->getId(),
                $courtOrder->getCourtOrderUid(),
                $courtOrder->getOrderType(),
                $courtOrder->getOrderReportType(),
                $courtOrder->getStatus(),
                $courtOrder->getOrderMadeDate()
            );
        }
        $dto->setCourtOrders($courtOrders);

        return $dto;
    }
}
