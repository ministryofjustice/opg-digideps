<?php

namespace OPG\Digideps\Backend\v2\Assembler\Report;

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
        /** @var array $courtOrdersRaw */
        $courtOrdersRaw = $data['courtOrders'];

        /** @var array $courtOrderData */
        foreach ($courtOrdersRaw as $courtOrderData) {
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
}
