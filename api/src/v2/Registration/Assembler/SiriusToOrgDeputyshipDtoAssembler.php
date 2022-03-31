<?php

declare(strict_types=1);

namespace App\v2\Registration\Assembler;

use App\Entity\User;
use App\Service\ReportUtils;
use App\v2\Registration\DTO\OrgDeputyshipDto;

class SiriusToOrgDeputyshipDtoAssembler
{
    /**
     * @var ReportUtils
     */
    private $reportUtils;

    public function __construct(ReportUtils $reportUtils)
    {
        $this->reportUtils = $reportUtils;
    }

    public function assembleMultipleDtosFromArray(array $rows)
    {
        $dtos = [];

        foreach ($rows as $row) {
            $dtos[] = $this->assembleSingleDtoFromArray($row);
        }

        return $dtos;
    }

    public function assembleSingleDtoFromArray(array $row): OrgDeputyshipDto
    {
        //Use the new function implemented by Alex to determine report type
//        $reportType = $this->reportUtils->convertTypeofRepAndCorrefToReportType(
//            $row['Typeofrep'],
//            $row['Corref'],
//            User::$depTypeIdToRealm[$row['Dep Type']]
//        );

        $reportEndDate = new \DateTime($row['LastReportDay']);
        $reportStartDate = $reportEndDate ? $this->reportUtils->generateReportStartDateFromEndDate($reportEndDate) : null;

        return (new OrgDeputyshipDto())
            ->setCaseNumber($row['Case'])
            ->setClientFirstname($row['ClientForename'])
            ->setClientLastname($row['ClientSurname'])
            ->setClientDateOfBirth($row['ClientDateOfBirth'])
            ->setClientAddress1($row['ClientAddress1'])
            ->setClientAddress2($row['ClientAddress2'])
            ->setClientAddress3($row['ClientAddress3'])
            ->setClientAddress4($row['ClientAddress4'])
            ->setClientAddress5($row['ClientAddress5'])
            ->setClientPostCode($row['ClientPostcode'])
            ->setDeputyUid($row['DeputyUid'])
            ->setDeputyEmail($row['Email'])
            ->setDeputyFirstname($row['DeputyForename'])
            ->setDeputyLastname($row['DeputySurname'])
            ->setDeputyAddress1($row['DeputyAddress1'])
            ->setDeputyAddress2($row['DeputyAddress2'])
            ->setDeputyAddress3($row['DeputyAddress3'])
            ->setDeputyAddress4($row['DeputyAddress4'])
            ->setDeputyAddress5($row['DeputyAddress5'])
            ->setDeputyPostcode($row['DeputyPostcode'])
            ->setCourtDate($row['MadeDate'])
            ->setReportStartDate($reportStartDate)
            ->setReportEndDate($reportEndDate)

            ->setReportType($reportType);
    }
}
