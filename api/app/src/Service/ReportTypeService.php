<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Service;

use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\CourtOrder;

class ReportTypeService
{
    /**
     * @param CourtOrder[] $courtOrders
     */
    public static function determineReportType(array $courtOrders): ?ReportType
    {
        if (count($courtOrders) === 0) {
            return null;
        }

        $reportTypes = array_unique(
            array_map(function (CourtOrder $courtOrder) {
                return $courtOrder->getDesiredReportType();
            }, $courtOrders)
        );

        if (count($reportTypes) > 1) {
            return null;
        }

        return $reportTypes[0];
    }
}
