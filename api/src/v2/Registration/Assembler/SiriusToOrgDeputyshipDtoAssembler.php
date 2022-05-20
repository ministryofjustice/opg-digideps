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

        $reportEndDate = $this->processDate($row['LastReportDay']);
        $reportStartDate = $reportEndDate ? $this->reportUtils->generateReportStartDateFromEndDate($reportEndDate) : null;
        $madeDate = $this->processDate($row['MadeDate']);
        $dateOfBirth = $this->processDate($row['ClientDateOfBirth']);

        return (new OrgDeputyshipDto())
            ->setCaseNumber($row['Case'])
            ->setClientFirstname($row['ClientForename'])
            ->setClientLastname($row['ClientSurname'])
            ->setClientDateOfBirth($dateOfBirth)
            ->setClientAddress1($row['ClientAddress1'])
            ->setClientAddress2($row['ClientAddress2'])
            ->setClientAddress3($row['ClientAddress3'])
            ->setClientAddress4($row['ClientAddress4'])
            ->setClientAddress5($row['ClientAddress5'])
            ->setClientPostCode($row['ClientPostcode'])
            ->setCourtDate($madeDate)
            ->setDeputyAddress1($row['DeputyAddress1'])
            ->setDeputyAddress2($row['DeputyAddress2'])
            ->setDeputyAddress3($row['DeputyAddress3'])
            ->setDeputyAddress4($row['DeputyAddress4'])
            ->setDeputyAddress5($row['DeputyAddress5'])
            ->setDeputyEmail($row['DeputyEmail'])
            ->setDeputyFirstname($row['DeputyForename'])
            ->setDeputyLastname($row['DeputySurname'])
            ->setDeputyPostcode($row['DeputyPostcode'])
            ->setDeputyUid($row['DeputyUid'])
            ->setOrganisationName($row['DeputyOrganisation'])
            ->setReportStartDate($reportStartDate)
            ->setReportEndDate($reportEndDate)
            ->setReportType($reportType);
    }

    public function processDate(string $date): ?DateTime
    {
        if ($date) {
            $result = DateTime::createFromFormat('Y-m-d', $date);

            if (false != $result) {
                return $result;
            } else {
                return null;
            }
        }

        return null;
    }
}
