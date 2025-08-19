<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;

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
        string $orderType,
        \DateTime $orderDate,
        string $realm = PreRegistration::REALM_LAY): Report
    {
        $determinedReportType = PreRegistration::getReportTypeByOrderType($typeOfReport, $orderType, $realm);

        $reportStartDate = clone $orderDate;
        $reportEndDate = clone $reportStartDate;
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
