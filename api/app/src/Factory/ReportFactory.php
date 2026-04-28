<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\Report;

class ReportFactory
{
    /**
     * @param string $typeOfReport e.g. "OPG102"
     * @param string $orderType    e.g. "hw"
     * @param string $realm        one of the PreRegistration::REALM_* constants
     */
    public function create(
        Client $client,
        string $typeOfReport,
        ?string $orderType,
        \DateTime $orderDate,
        string $realm = PreRegistration::REALM_LAY
    ): Report {
        if (is_null($orderType)) {
            $orderType = '';
        }

        $determinedReportType = PreRegistration::getReportTypeByOrderType($typeOfReport, $orderType, $realm);

        $reportStartDate = clone $orderDate;

        $reportEndDate = clone $orderDate;
        $reportEndDate->add(new \DateInterval('P364D'));

        return new Report(
            $client,
            $determinedReportType,
            $reportStartDate,
            $reportEndDate,
            false
        );
    }
}
