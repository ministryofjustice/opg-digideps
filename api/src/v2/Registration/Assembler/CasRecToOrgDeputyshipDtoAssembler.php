<?php declare(strict_types=1);


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
        $caseNumber = $this->reportUtils->padCasRecNumber(strtolower($row['Case']));
        $deputyNumber = $this->reportUtils->padCasRecNumber(strtolower($row['Deputy No']));

        return (new OrgDeputyshipDto())
            ->setDeputyEmail($row['Email'])
            ->setDeputyNumber($deputyNumber)
            ->setDeputyFirstname($row['Dep Forename'])
            ->setDeputyLastname($row['Dep Surname'])
            ->setDeputyAddress1($row['Dep Adrs1'])
            ->setDeputyPostcode($row['Dep Postcode'])
            ->setCaseNumber($caseNumber)
            ->setClientFirstname(trim($row['Forename']))
            ->setClientLastname(trim($row['Surname']))
            ->setClientAddress1($row['Client Adrs1'])
            ->setClientAddress2($row['Client Adrs2'])
            ->setClientCounty($row['Client Adrs3'])
            ->setClientPostCode($row['Client Postcode'])
            ->setClientDateOfBirth($clientDateOfBirth)
            ->setCourtDate(new DateTime($row['Made Date']))
            ->setReportType($reportType)
            ->setReportStartDate($reportStartDate)
            ->setReportEndDate($reportEndDate);
    }
}
