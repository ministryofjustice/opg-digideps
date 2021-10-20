<?php

declare(strict_types=1);

namespace App\v2\Registration\Assembler;

use App\Entity\User;
use App\Service\ReportUtils;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use DateTime;

class CasRecToOrgDeputyshipDtoAssembler
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

    public function assembleSingleDtoFromArray(array $row)
    {
        $reportType = $this->reportUtils->convertTypeofRepAndCorrefToReportType(
            $row['Typeofrep'],
            $row['Corref'],
            User::$depTypeIdToRealm[$row['Dep Type']]
        );

        $clientDateOfBirth = $this->reportUtils->parseCsvDate($row['Client Date of Birth'], '19');
        $reportEndDate = $this->reportUtils->parseCsvDate($row['Last Report Day'], '20');
        $reportStartDate = $reportEndDate ? $this->reportUtils->generateReportStartDateFromEndDate($reportEndDate) : null;

        // Adding padding as entities that use DTO data pad case number, deputy number and deputy address number - needs to match for DB lookups on these values
        $caseNumber = $this->reportUtils->padCasRecNumber(strtolower($row['Case']));
        $deputyNumber = User::padDeputyNumber(strtolower($row['Deputy No']));
        // DepAddr No column is missing from PA CSV uploads
        $deputyAddressNumber = !isset($row['DepAddr No']) ? null : User::padDeputyNumber(strtolower($row['DepAddr No']));

        $courtDate = empty($row['Made Date']) ? null : new DateTime($row['Made Date']);

        return (new OrgDeputyshipDto())
            ->setDeputyEmail($row['Email'])
            ->setDeputyNumber($deputyNumber)
            ->setDeputyFirstname($row['Dep Forename'])
            ->setDeputyLastname($row['Dep Surname'])
            ->setDeputyAddress1($row['Dep Adrs1'])
            ->setDeputyAddress2($row['Dep Adrs2'])
            ->setDeputyAddress3($row['Dep Adrs3'])
            ->setDeputyAddress4($row['Dep Adrs4'])
            ->setDeputyAddress5($row['Dep Adrs5'])
            ->setDeputyPostcode($row['Dep Postcode'])
            ->setCaseNumber($caseNumber)
            ->setClientFirstname(trim($row['Forename']))
            ->setClientLastname(trim($row['Surname']))
            ->setClientAddress1($row['Client Adrs1'])
            ->setClientAddress2($row['Client Adrs2'])
            ->setClientCounty($row['Client Adrs3'])
            ->setClientPostCode($row['Client Postcode'])
            ->setClientDateOfBirth($clientDateOfBirth)
            ->setCourtDate($courtDate)
            ->setReportType($reportType)
            ->setReportStartDate($reportStartDate)
            ->setReportEndDate($reportEndDate)
            ->setDeputyAddressNumber($deputyAddressNumber)
            ->setDeputyType($row['Dep Type']);
    }
}
