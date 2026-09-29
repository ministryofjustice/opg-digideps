<?php

namespace OPG\Digideps\Backend\v2\Assembler\Report;

use OPG\Digideps\Backend\v2\DTO\CourtOrderDTO;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\DTO\ReportDto;
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

        $courtOrders = array_map(function (array $courtOrderData) {
            $courtOrderDataValidated = new ValidatingArray($courtOrderData);

            return new CourtOrderDTO(
                $courtOrderDataValidated->getIntegerOrThrow('id'),
                $courtOrderDataValidated->getStringOrThrow('courtOrderUid'),
                CourtOrderType::tryFrom($courtOrderDataValidated->getStringOrThrow('orderType')),
                CourtOrderReportType::tryFrom($courtOrderDataValidated->getStringOrThrow('orderReportType')),
                $courtOrderDataValidated->getStringOrThrow('status'),
                $courtOrderDataValidated->getObjectOrThrow('orderMadeDate', \DateTime::class)
            );
        }, $data['courtOrders']);

        $dto->setCourtOrders($courtOrders);

        return $dto;
    }
}
