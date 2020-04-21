<?php

namespace AppBundle\v2\Assembler\CourtOrder;

use AppBundle\Entity\CourtOrder;

abstract class AbstractCourtOrderDtoAssembler
{
    /**
     * @param string $corref
     * @return string
     */
    protected function determineType(string $corref): string
    {
        return strtolower($corref) === 'hw' ? CourtOrder::SUBTYPE_HW : CourtOrder::SUBTYPE_PFA;
    }

    /**
     * @param string|null $reportType
     * @return string|null
     */
    protected function determineSupervisionLevel($reportType): ?string
    {
        if (strtolower($reportType) == 'opg102') {
            return CourtOrder::LEVEL_GENERAL;
        } else if (strtolower($reportType) == 'opg103') {
            return CourtOrder::LEVEL_MINIMAL;
        }

        return null;
    }
}
