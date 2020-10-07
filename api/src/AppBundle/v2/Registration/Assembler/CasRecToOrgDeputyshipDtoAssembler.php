<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Assembler;

use AppBundle\Entity\User;
use AppBundle\Service\ReportUtils;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
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
        $reportStartDate = $this->reportUtils->generateReportStartDateFromEndDate($reportEndDate);
        $caseNumber = $this->reportUtils->padCaseNumber(strtolower($row['Case']));

        return (new OrgDeputyshipDto())
            ->setDeputyEmail($row['Email'])
            ->setDeputyNumber($row['Deputy No'])
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

//        Case
//        Forename
//        Surname
//        Client Adrs1
//        Client Adrs2
//        Client Adrs3
//        Client Adrs4
//        Client Adrs5
//        Client Postcode
//        Client Date of Birth
//        Dep Type
//        Email
//        Email2
//        Email3
//        Deputy No
//        Dep Forename
//        Dep Surname
//        DepAddr No
//        Dep Adrs1
//        Dep Adrs2
//        Dep Adrs3
//        Dep Adrs4
//        Dep Adrs5
//        Dep Postcode
//        Made Date
//        Dig
//        Digdate	Foreign	Corref
//        Last Report Day
//        Typeofrep
//        Team
//        Fee Payer
//        Corres
//        Sett Comp
    }
}
