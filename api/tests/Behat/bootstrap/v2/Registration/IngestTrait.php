<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\CasRec;
use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Tests\Behat\BehatException;
use Behat\Gherkin\Node\TableNode;
use DateTime;

trait IngestTrait
{
    private array $clients = ['expected' => 0, 'found' => 0];
    private array $namedDeputies = ['expected' => 0, 'found' => 0];
    private array $organisations = ['expected' => 0, 'found' => 0];
    private array $reports = ['expected' => 0, 'found' => 0];
    private array $casrec = ['expected' => 0, 'found' => 0];
    private array $expectedMissingDTOProperties = [];
    private array $entityUids = [
        'client_case_numbers' => [],
        'named_deputy_numbers' => [],
        'org_email_identifiers' => [],
        'casrec_case_numbers' => [],
    ];

    private ?DateTime $expectedClientCourtDate = null;

    private string $expectedNamedDeputyName = '';
    private string $expectedNamedDeputyAddress = '';
    private string $expectedReportType = '';
    private string $expectedCaseNumberAssociatedWithError = '';
    private string $expectedUnexpectedColumn = '';

    /**
     * @When I upload a :source org CSV that contains the following new entities:
     */
    public function iUploadACsvThatContainsTheFollowingNewEntities(string $source, TableNode $table)
    {
        $this->iamOnAdminUploadUsersPage();

        if ('casrec' === $source) {
            $hash = $table->getHash();

            if (count($hash) > 1) {
                throw new BehatException('Only a single row of entity numbers is supported. Remove additional rows from the test.');
            }

            $this->clients['expected'] = intval($hash[0]['clients']);
            $this->namedDeputies['expected'] = intval($hash[0]['named_deputies']);
            $this->organisations['expected'] = intval($hash[0]['organisations']);
            $this->reports['expected'] = intval($hash[0]['reports']);

            $this->selectOption('form[type]', 'org');
            $this->pressButton('Continue');

            $this->uploadCsvAndCountCreatedEntities(
                'casrec-csvs/org-3-valid-rows.csv',
                'Upload PA/Prof users'
            );
        } elseif ('sirius' === $source) {
            // Add Sirius steps
        } else {
            throw new BehatException('$source should be casrec or sirius');
        }
    }

    /**
     * @Then the new :type entities should be added to the database
     */
    public function theNewEntitiesShouldBeAddedToTheDatabase(string $type)
    {
        $this->iAmOnCorrectUploadPage($type);

        if ('org' === $type) {
            $this->assertIntEqualsInt($this->clients['expected'], $this->clients['found'], 'Count of entities based on UIDs - clients');
            $this->assertIntEqualsInt($this->namedDeputies['expected'], $this->namedDeputies['found'], 'Count of entities based on UIDs - named deputies');
            $this->assertIntEqualsInt($this->organisations['expected'], $this->organisations['found'], 'Count of entities based on UIDs - organisations');
            $this->assertIntEqualsInt($this->reports['expected'], $this->reports['found'], 'Count of entities based on UIDs - reports');
        } else {
            $this->assertIntEqualsInt($this->casrec['expected'], $this->casrec['found'], 'Count of entities based on UIDs - casrec');
        }
    }

    /**
     * @Then the count of the new :type entities added should be displayed on the page
     */
    public function theNewEntitiesCountShouldBeDisplayed(string $type)
    {
        $this->iAmOnCorrectUploadPage($type);

        if ('org' === $type) {
            $this->assertOnAlertMessage(sprintf('%s clients', $this->clients['expected']));
            $this->assertOnAlertMessage(sprintf('%s named deputies', $this->namedDeputies['expected']));
            $this->assertOnAlertMessage(sprintf('%s organisation', $this->organisations['expected']));
            $this->assertOnAlertMessage(sprintf('%s reports', $this->reports['expected']));
        } else {
            $this->assertOnAlertMessage(sprintf('%s record uploaded', $this->casrec['expected']));
        }
    }

    private function extractUidsFromCsv($csvFilePath)
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$csvFilePath;
            if (is_file($fullPath)) {
                $csvFilePath = $fullPath;
            }
        }

        $csvRows = array_map('str_getcsv', file($csvFilePath));
        array_walk($csvRows, function (&$a) use ($csvRows) {
            $a = array_combine($csvRows[0], $a);
        });
        array_shift($csvRows); // remove column header

        foreach ($csvRows as $row) {
            $email = empty($row['Email']) ? null : substr(strstr($row['Email'], '@'), 1);

            $this->entityUids['client_case_numbers'][] = $row['Case'];
            $this->entityUids['casrec_case_numbers'][] = strtolower($row['Case'] ?: '');
            $this->entityUids['named_deputy_numbers'][] = $row['Deputy No'];
            $this->entityUids['org_email_identifiers'][] = $email;
        }

        $this->entityUids['client_case_numbers'] = array_unique($this->entityUids['client_case_numbers']);
        $this->entityUids['casrec_case_numbers'] = array_unique($this->entityUids['casrec_case_numbers']);
        $this->entityUids['named_deputy_numbers'] = array_unique($this->entityUids['named_deputy_numbers']);
        $this->entityUids['org_email_identifiers'] = array_unique($this->entityUids['org_email_identifiers']);
    }

    private function countCreatedEntities()
    {
        $this->em->clear();

        $clients = $this->em->getRepository(Client::class)->findBy(['caseNumber' => $this->entityUids['client_case_numbers']]);
        $namedDeputies = $this->em->getRepository(NamedDeputy::class)->findBy(['deputyNo' => $this->entityUids['named_deputy_numbers']]);
        $orgs = $this->em->getRepository(Organisation::class)->findBy(['emailIdentifier' => $this->entityUids['org_email_identifiers']]);
        $casrecs = $this->em->getRepository(CasRec::class)->findBy(['caseNumber' => $this->entityUids['casrec_case_numbers']]);

        $reports = [];

        foreach ($clients as $client) {
            foreach ($client->getReports() as $report) {
                $reports[] = $report;
            }
        }

        $this->clients['found'] = count($clients);
        $this->namedDeputies['found'] = count($namedDeputies);
        $this->organisations['found'] = count($orgs);
        $this->casrec['found'] = count($casrecs);
        $this->reports['found'] = count($reports);
    }

    /**
     * @When I upload a :source org CSV that has a new made date :newMadeDate and named deputy :newNamedDeputy within the same org as the clients existing name deputy
     */
    public function iUploadACsvThatHasANewMadeDateAndNamedDeputyWithinTheSameOrgAsTheClientsExistingNameDeputy(string $source, string $newMadeDate, string $newNamedDeputy)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedClientCourtDate = new DateTime($newMadeDate);
        $this->expectedNamedDeputyName = $newNamedDeputy;

        $this->createProfAdminNotStarted(null, 'professor@mccracken4.com', '40000000');

        $this->uploadCsvAndCountCreatedEntities(
            'casrec-csvs/org-1-updated-row-made-date-and-named-deputy.csv',
            'Upload PA/Prof users'
        );
    }

    private function uploadCsvAndCountCreatedEntities(string $csvFilepath, string $uploadButtonText)
    {
        $this->attachFileToField('admin_upload[file]', $csvFilepath);
        $this->pressButton($uploadButtonText);
        $this->waitForAjaxAndRefresh();

        $this->extractUidsFromCsv($csvFilepath);
        $this->countCreatedEntities();
    }

    /**
     * @Then the clients made date and named deputy should be updated
     */
    public function theClientsMadeDateAndNamedDeputyShouldBeUpdated()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->em->clear();
        $client = $this->em->getRepository(Client::class)->find($this->profAdminDeputyNotStartedDetails->getClientId());

        $this->assertStringEqualsString(
            $this->expectedClientCourtDate->format('j F Y'),
            $client->getCourtDate()->format('j F Y'),
            'Comparing expected court date to client court date'
        );

        $this->assertStringEqualsString(
            $this->expectedNamedDeputyName,
            sprintf('%s %s', $client->getNamedDeputy()->getFirstName(), $client->getNamedDeputy()->getLastName()),
            'Comparing expected named deputy full name to client named deputy full name'
        );
    }

    /**
     * @When I upload a :source org CSV that has a new address :address for an existing named deputy
     */
    public function iUploadACsvThatHasANewAddressAndPhoneDetailsForAnExistingNamedDeputy(string $source, string $address)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedNamedDeputyAddress = $address;

        $this->createProfAdminNotStarted(null, 'him@jojo5.com', '50000000', '66648');

        $this->uploadCsvAndCountCreatedEntities(
            'casrec-csvs/org-1-updated-row-named-deputy-address.csv',
            'Upload PA/Prof users'
        );
    }

    /**
     * @Then the named deputy's address should be updated
     */
    public function theNamedDeputiesAddressShouldBeUpdated()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->em->clear();

        $namedDeputy = ($this->em
            ->getRepository(Client::class)
            ->find($this->profAdminDeputyNotStartedDetails->getClientId()))
            ->getNamedDeputy();

        $actualNamedDeputiesAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $namedDeputy->getAddress1(),
            $namedDeputy->getAddress2(),
            $namedDeputy->getAddress3(),
            $namedDeputy->getAddress4(),
            $namedDeputy->getAddress5(),
            $namedDeputy->getAddressPostcode()
        );

        $this->assertStringEqualsString(
            $this->expectedNamedDeputyAddress,
            $actualNamedDeputiesAddress,
            'Comparing expected named deputy address to actual named deputy address'
        );
    }

    /**
     * @When I upload a :source org CSV that has a new report type :reportTypeNumber for an existing report that has not been submitted or unsubmitted
     */
    public function iUploadACsvThatHasANewReportType(string $source, string $reportTypeNumber)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedReportType = $reportTypeNumber;

        $this->createProfAdminNotStarted(null, 'fuzzy.lumpkins@jojo6.com', '60000000', '112233');

        $this->uploadCsvAndCountCreatedEntities(
            'casrec-csvs/org-1-updated-row-report-type.csv',
            'Upload PA/Prof users'
        );
    }

    /**
     * @Then the report type should be updated
     */
    public function theReportTypeShouldBeUpdated()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->em->clear();

        $currentReport = $this->em
            ->getRepository(Report::class)
            ->find($this->profAdminDeputyNotStartedDetails->getCurrentReportId());

        $this->assertStringEqualsString(
            $this->expectedReportType,
            $currentReport->getType(),
            'Comparing expected named deputy address to actual named deputy address'
        );
    }

    /**
     * @When I upload a :source org CSV that has 1 row with missing values 'Last Report Day, Made Date, Email' for case number :caseNumber and 1 valid row
     */
    public function iUploadACsvThatHasMissingValueAndOneValidRow(string $source, string $caseNumber)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedMissingDTOProperties = ['Report Start Date', 'Report End Date', 'Court Date', 'Deputy Email'];
        $this->expectedCaseNumberAssociatedWithError = $caseNumber;

        $this->clients['expected'] = 1;
        $this->namedDeputies['expected'] = 1;
        $this->organisations['expected'] = 1;
        $this->reports['expected'] = 1;

        $this->createProfAdminNotStarted();

        $this->uploadCsvAndCountCreatedEntities(
            'casrec-csvs/org-1-row-missing-last-report-date-1-valid-row.csv',
            'Upload PA/Prof users'
        );
    }

    /**
     * @Then I should see an error showing the problem on the :type csv upload page
     */
    public function iShouldSeeErrorShowingProblem(string $type)
    {
        $this->iAmOnCorrectUploadPage($type);

        foreach ($this->expectedMissingDTOProperties as $expectedMissingDTOProperty) {
            $this->assertOnErrorMessage($expectedMissingDTOProperty);
        }

        $this->assertOnErrorMessage($this->expectedCaseNumberAssociatedWithError);
    }

    /**
     * @When I upload a :source :userType CSV that does not have any of the required columns
     */
    public function iUploadACsvThatHasMissingDeputyNoColumn(string $source, string $userType)
    {
        if (!in_array($source, ['casrec', 'sirius'])) {
            throw new BehatException('$source should be casrec or sirius');
        }

        $this->iAmOnCorrectUploadPage($userType);

        if ('casrec' === $source) {
            $csvFilepath = ('org' === $userType) ? 'casrec-csvs/org-1-row-missing-all-required-columns.csv' : 'casrec-csvs/lay-1-row-missing-all-required-columns.csv';
        } else {
            $csvFilepath = 'sirius-csvs/lay-1-row-missing-all-required-columns.csv';
        }

        $buttonText = ('org' === $userType) ? 'Upload PA/Prof users' : 'Upload Lay users';

        $this->uploadCsvAndCountCreatedEntities($csvFilepath, $buttonText);
    }

    /**
     * @Then I should see an error showing which :source columns are missing on the :userType csv upload page
     */
    public function iShouldSeeErrorShowingMissingColumns(string $source, string $userType)
    {
        $this->iAmOnCorrectUploadPage($userType);

        if ('org' === strtolower($userType)) {
            $requiredColumns = [
                'Deputy No',
                'Dep Postcode',
                'Dep Forename',
                'Dep Surname',
                'Dep Type',
                'Dep Adrs1',
                'Dep Adrs2',
                'Dep Adrs3',
                'Dep Adrs4',
                'Dep Adrs5',
                'Dep Postcode',
                'Email',
                'Email2',
                'Email3',
                'Case',
                'Forename',
                'Surname',
                'Corref',
                'Typeofrep',
                'Last Report Day',
                'Made Date',
            ];
        } else {
            $requiredColumns = [
                'Case',
                'Surname',
                'Deputy No',
                'Dep Surname',
                'Dep Postcode',
                'Typeofrep',
                'Made Date',
            ];

            if ('casrec' === $source) {
                array_push($requiredColumns, 'Corref', 'NDR');
            }
        }

        foreach ($requiredColumns as $requiredColumn) {
            $this->assertOnErrorMessage($requiredColumn);
        }
    }

    /**
     * @When I upload a :source org CSV that has an/a :columnName column
     */
    public function iUploadACsvThatHasNdrColumn(string $source, string $columnName)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedUnexpectedColumn = $columnName;

        $this->uploadCsvAndCountCreatedEntities(
            'casrec-csvs/org-1-row-with-ndr-column.csv',
            'Upload PA/Prof users'
        );
    }

    /**
     * @Then I should see an error showing the column that was unexpected
     */
    public function iShouldSeeErrorShowingUnexpectedColumns()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->assertOnErrorMessage($this->expectedUnexpectedColumn);
    }

    /**
     * @When I upload a :source lay CSV that contains :newEntitiesCount new casrec entities
     */
    public function iUploadCsvContaining3CasrecEntities(string $source, int $newEntitiesCount)
    {
        if (!in_array($source, ['casrec', 'sirius'])) {
            throw new BehatException('$source should be casrec or sirius');
        }

        $this->iamOnAdminUploadUsersPage();

        $this->casrec['expected'] = $newEntitiesCount;

        $this->selectOption('form[type]', 'lay');
        $this->pressButton('Continue');

        $filePath = 'casrec' === $source ? 'casrec-csvs/lay-3-valid-rows.csv' : 'sirius-csvs/lay-3-valid-rows.csv';

        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload Lay users');
    }

    private function iAmOnCorrectUploadPage(string $type)
    {
        if (!in_array(strtolower($type), ['org', 'lay'])) {
            throw new BehatException('$type can only be lay or org');
        }

        'org' === $type ? $this->iAmOnAdminOrgCsvUploadPage() : $this->iAmOnAdminLayCsvUploadPage();
    }

    /**
     * @When I upload a :source lay CSV that has a new report type :reportTypeNumber and corref for case number :caseNumber
     */
    public function iUploadLayCsvWithNewReportType(string $source, string $reportTypeNumber, string $caseNumber)
    {
        $this->iAmOnAdminLayCsvUploadPage();

        $this->expectedReportType = $reportTypeNumber;

        $this->createPfaHighNotStarted($caseNumber);

        $filePath = 'casrec' === $source ? 'casrec-csvs/lay-1-row-updated-report-type.csv' : 'sirius-csvs/lay-1-row-updated-report-type.csv';

        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload Lay users');
    }

    /**
     * @Then the clients report type should be updated
     */
    public function theClientsReportTypeShouldBeUpdated()
    {
        $this->iAmOnAdminLayCsvUploadPage();

        $this->em->clear();
        $client = $this->em->getRepository(Client::class)->find($this->layDeputyNotStartedPfaHighAssetsDetails->getClientId());

        $this->assertStringEqualsString(
            $this->expectedReportType,
            $client->getCurrentReport()->getType(),
            'Comparing expected report type to clients report type'
        );
    }

    /**
     * @When I upload a :source lay CSV that has 1 row with missing values for 'caseNumber, clientLastname, DeputyNo and deputySurname' and :newEntitiesCount valid row
     */
    public function iUploadCsvWith1ValidAnd1InvalidRow(string $source, int $newEntitiesCount)
    {
        $this->iAmOnAdminLayCsvUploadPage();

        $this->expectedMissingDTOProperties = ['caseNumber', 'clientLastname', 'deputyNo', 'deputySurname'];
        $this->casrec['expected'] = $newEntitiesCount;

        $filePath = 'casrec' === $source ? 'casrec-csvs/lay-1-row-missing-all-required-1-valid-row.csv' : 'sirius-csvs/lay-1-row-missing-all-required-1-valid-row.csv';

        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload Lay users');
    }
}
