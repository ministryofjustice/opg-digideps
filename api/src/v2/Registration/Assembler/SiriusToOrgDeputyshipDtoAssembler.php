<?php

declare(strict_types=1);

namespace App\v2\Registration\Assembler;

use App\Service\ReportUtils;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use DateTime;

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
        $reportType = $this->reportUtils->determineReportType($row['ReportType'], $row['OrderType'], $row['DeputyType']);

        $reportEndDate = new \DateTime($row['LastReportDay']);
        $reportStartDate = $reportEndDate ? $this->reportUtils->generateReportStartDateFromEndDate($reportEndDate) : null;

        return (new OrgDeputyshipDto())
            ->setCaseNumber($row['Case'])
            ->setClientFirstname($row['ClientForename'])
            ->setClientLastname($row['ClientSurname'])
            ->setClientDateOfBirth(new DateTime($row['ClientDateOfBirth']))
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
            ->setCourtDate(new DateTime($row['MadeDate']))
            ->setReportStartDate($reportStartDate)
            ->setReportEndDate($reportEndDate)
            ->setReportType($reportType);
    }
}
