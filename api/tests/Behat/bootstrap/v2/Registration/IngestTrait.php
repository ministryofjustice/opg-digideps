<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\PreRegistration;
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
    private array $preRegistration = ['expected' => 0, 'found' => 0];
    private array $expectedMissingDTOProperties = [];
    public array $entityUids = [
        'client_case_numbers' => [],
        'named_deputy_uids' => [],
        'org_email_identifiers' => [],
        'sirius_case_numbers' => [],
    ];

    private ?DateTime $expectedClientCourtDate = null;

    private string $expectedNamedDeputyName = '';
    private string $expectedNamedDeputyAddress = '';
    private string $expectedReportType = '';
    private string $expectedCaseNumberAssociatedWithError = '';
    private string $expectedUnexpectedColumn = '';

    private $clientBeforeCsvUpload;
    private $clientAfterCsvUpload;

    /**
     * @When I upload an org CSV that contains the following new entities:
     */
    public function iUploadAnOrgCsvThatContainsTheFollowingNewEntities(TableNode $table)
    {
        $this->iamOnAdminUploadUsersPage();

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
            'sirius-csvs/org-3-valid-rows.csv',
            'Upload PA/Prof users'
        );
    }

    /**
     * @Then the new :type entities should be added to the database
     */
    public function theNewEntitiesShouldBeAddedToTheDatabase(string $type)
    {
        $this->iAmOnCorrectUploadPage($type);

        if (in_array(strtolower($type), ['org', 'pa'])) {
            $this->assertIntEqualsInt($this->clients['expected'], $this->clients['found'], 'Count of entities based on UIDs - clients');
            $this->assertIntEqualsInt($this->namedDeputies['expected'], $this->namedDeputies['found'], 'Count of entities based on UIDs - named deputies');
            $this->assertIntEqualsInt($this->organisations['expected'], $this->organisations['found'], 'Count of entities based on UIDs - organisations');
            $this->assertIntEqualsInt($this->reports['expected'], $this->reports['found'], 'Count of entities based on UIDs - reports');
        } else {
            $this->assertIntEqualsInt($this->preRegistration['expected'], $this->preRegistration['found'], 'Count of entities based on UIDs - Pre-registration');
        }
    }

    /**
     * @Then the count of the new :type entities added should be displayed on the page
     */
    public function theNewEntitiesCountShouldBeDisplayed(string $type)
    {
        $this->iAmOnCorrectUploadPage($type);

        if (in_array(strtolower($type), ['org', 'pa'])) {
            $this->assertOnAlertMessage(sprintf('%s clients', $this->clients['expected']));
            $this->assertOnAlertMessage(sprintf('%s named deputies', $this->namedDeputies['expected']));
            $this->assertOnAlertMessage(sprintf('%s organisation', $this->organisations['expected']));
            $this->assertOnAlertMessage(sprintf('%s reports', $this->reports['expected']));
        } else {
            $this->assertOnAlertMessage(sprintf('%s record uploaded', $this->preRegistration['expected']));
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
            $email = empty($row['DeputyEmail']) ? null : substr(strstr($row['DeputyEmail'], '@'), 1);

            $this->entityUids['client_case_numbers'][] = $row['Case'] ?? null;
            $this->entityUids['sirius_case_numbers'][] = $row['Case'] ?? '';
            $this->entityUids['named_deputy_uids'][] = $row['DeputyUid'] ?? '';
            $this->entityUids['org_email_identifiers'][] = $email;
        }

        $this->entityUids['client_case_numbers'] = array_unique($this->entityUids['client_case_numbers']);
        $this->entityUids['sirius_case_numbers'] = array_unique($this->entityUids['sirius_case_numbers']);
        $this->entityUids['named_deputy_uids'] = array_unique($this->entityUids['named_deputy_uids']);
        $this->entityUids['org_email_identifiers'] = array_unique($this->entityUids['org_email_identifiers']);
    }

    private function countCreatedEntities()
    {
        $this->em->clear();

        $clients = $this->em->getRepository(Client::class)->findBy(['caseNumber' => $this->entityUids['client_case_numbers']]);
        $namedDeputies = $this->em->getRepository(NamedDeputy::class)->findBy(['deputyUid' => $this->entityUids['named_deputy_uids']]);
        $orgs = $this->em->getRepository(Organisation::class)->findBy(['emailIdentifier' => $this->entityUids['org_email_identifiers']]);
        $preRegistrations = $this->em->getRepository(PreRegistration::class)->findBy(['caseNumber' => $this->entityUids['sirius_case_numbers']]);

        $reports = [];

        foreach ($clients as $client) {
            foreach ($client->getReports() as $report) {
                $reports[] = $report;
            }
        }

        $this->clients['found'] = count($clients);
        $this->namedDeputies['found'] = count($namedDeputies);
        $this->organisations['found'] = count($orgs);
        $this->preRegistration['found'] = count($preRegistrations);
        $this->reports['found'] = count($reports);
    }

    /**
     * @When I upload an org CSV that has a new named deputy :newNamedDeputy within the same org as the clients existing name deputy
     */
    public function iUploadAnOrgCsvThatHasANewMadeDateAndNamedDeputyWithinTheSameOrgAsTheClientsExistingNameDeputy(string $newNamedDeputy)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedNamedDeputyName = $newNamedDeputy;

        $this->createProfAdminNotStarted(null, 'professor@mccracken4.com', '40000000');

        $this->uploadCsvAndCountCreatedEntities(
            'sirius-csvs/org-1-updated-row-new-named-deputy.csv',
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
     * @Then the clients named deputy should be updated
     */
    public function theClientsNamedDeputyShouldBeUpdated()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->em->clear();
        $client = $this->em->getRepository(Client::class)->find($this->profAdminDeputyHealthWelfareNotStartedDetails->getClientId());

        $this->assertStringEqualsString(
            $this->expectedNamedDeputyName,
            sprintf('%s %s', $client->getNamedDeputy()->getFirstName(), $client->getNamedDeputy()->getLastName()),
            'Comparing expected named deputy full name to client named deputy full name'
        );
    }

    /**
     * @When I upload an org CSV that has a new address :address for an existing named deputy
     */
    public function iUploadACsvThatHasANewAddressAndPhoneDetailsForAnExistingNamedDeputy(string $address)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedNamedDeputyAddress = $address;

        $this->createProfAdminNotStarted(null, 'him@jojo5.com', '50000000', '66648');

        $this->uploadCsvAndCountCreatedEntities(
            'sirius-csvs/org-1-updated-row-named-deputy-address.csv',
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

        $namedDeputy = $this->em
            ->getRepository(Client::class)
            ->find($this->profAdminDeputyHealthWelfareNotStartedDetails->getClientId())
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
     * @When I upload an org CSV that has a new report type :reportTypeNumber for an existing report that has not been submitted or unsubmitted
     */
    public function iUploadACsvThatHasANewReportType(string $reportTypeNumber)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedReportType = $reportTypeNumber;

        $this->createProfAdminNotStarted(null, 'fuzzy.lumpkins@jojo6.com', '60000000', '740000000001');

        $this->uploadCsvAndCountCreatedEntities(
            'sirius-csvs/org-1-updated-row-report-type.csv',
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
            ->find($this->profAdminDeputyHealthWelfareNotStartedDetails->getCurrentReportId());

        $this->assertStringEqualsString(
            $this->expectedReportType,
            $currentReport->getType(),
            'Comparing expected report type to actual report type'
        );
    }

    /**
     * @When I upload an org CSV that has 1 row with missing values 'LastReportDay, MadeDate, DeputyEmail' for case number :caseNumber and 1 valid row
     */
    public function iUploadACsvThatHasMissingValueAndOneValidRow(string $caseNumber)
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
            'sirius-csvs/org-1-row-missing-last-report-date-1-valid-row.csv',
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
     * @Then I should see an alert showing the row was skipped on the :type csv upload page
     */
    public function iShouldSeeAnAlertShowingRowSkipped(string $type)
    {
        $this->iAmOnCorrectUploadPage($type);

        $this->assertOnAlertMessage('1 skipped');
    }

    /**
     * @When I upload a(n) :userType CSV that does not have any of the required columns
     */
    public function iUploadACsvThatHasMissingDeputyUidColumn(string $userType)
    {
        $this->iAmOnCorrectUploadPage($userType);

        if ('org' === $userType) {
            $csvFilepath = 'sirius-csvs/org-1-row-missing-all-required-columns.csv';
        } else {
            $csvFilepath = 'sirius-csvs/lay-1-row-missing-all-required-columns.csv';
        }

        $buttonText = ('org' === $userType) ? 'Upload PA/Prof users' : 'Upload Lay users';

        $this->uploadCsvAndCountCreatedEntities($csvFilepath, $buttonText);
    }

    /**
     * @Then I should see an error showing which columns are missing on the :userType csv upload page
     */
    public function iShouldSeeErrorShowingMissingColumns(string $userType)
    {
        $this->iAmOnCorrectUploadPage($userType);

        if ('org' === strtolower($userType)) {
            $requiredColumns = [
                'Case',
                'ClientForename',
                'ClientSurname',
                'ClientDateOfBirth',
                'ClientPostcode',
                'DeputyUid',
                'DeputyType',
                'DeputyEmail',
                'DeputyOrganisation',
                'DeputyForename',
                'DeputySurname',
                'DeputyPostcode',
                'MadeDate',
                'LastReportDay',
                'ReportType',
                'OrderType',
            ];
        } else {
            $requiredColumns = [
                'Case',
                'ClientSurname',
                'DeputyUid',
                'DeputySurname',
                'DeputyPostcode',
                'ReportType',
                'MadeDate',
                'OrderType',
                'CoDeputy',
            ];
        }

        foreach ($requiredColumns as $requiredColumn) {
            $this->assertOnErrorMessage($requiredColumn);
        }
    }

    /**
     * @When I upload an org CSV that has an/a :columnName column
     */
    public function iUploadACsvThatHasNdrColumn(string $columnName)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedUnexpectedColumn = $columnName;

        $this->uploadCsvAndCountCreatedEntities(
            'sirius-csvs/org-1-row-with-ndr-column.csv',
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
     * @When I upload a lay CSV that contains :newEntitiesCount new pre-registration entities
     */
    public function iUploadCsvContaining3PreRegistrationEntities(int $newEntitiesCount)
    {
        $this->iamOnAdminUploadUsersPage();

        $this->preRegistration['expected'] = $newEntitiesCount;

        $this->selectOption('form[type]', 'lay');
        $this->pressButton('Continue');

        $filePath = 'sirius-csvs/lay-3-valid-rows.csv';

        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload Lay users');
    }

    private function iAmOnCorrectUploadPage(string $type)
    {
        if (!in_array(strtolower($type), ['org', 'lay', 'pa'])) {
            throw new BehatException('$type can only be lay, pa or org');
        }

        in_array(strtolower($type), ['org', 'pa']) ? $this->iAmOnAdminOrgCsvUploadPage() : $this->iAmOnAdminLayCsvUploadPage();
    }

    /**
     * @When I upload a lay CSV that has a new report type :reportTypeNumber for case number :caseNumber
     */
    public function iUploadLayCsvWithNewReportType(string $reportTypeNumber, string $caseNumber)
    {
        $this->iAmOnAdminLayCsvUploadPage();

        $this->expectedReportType = $reportTypeNumber;

        $this->createPfaHighNotStarted(null, $caseNumber);

        $filePath = 'sirius-csvs/lay-1-row-updated-report-type.csv';

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
     * @When I upload a lay CSV that has 1 row with missing values for 'caseNumber, clientLastname, deputyUid and deputySurname' and :newEntitiesCount valid row
     */
    public function iUploadCsvWith1ValidAnd1InvalidRow(int $newEntitiesCount)
    {
        $this->iAmOnAdminLayCsvUploadPage();

        $this->expectedMissingDTOProperties = ['caseNumber', 'clientLastname', 'deputyUid', 'deputySurname'];
        $this->preRegistration['expected'] = $newEntitiesCount;

        $filePath = 'sirius-csvs/lay-1-row-missing-all-required-1-valid-row.csv';

        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload Lay users');
    }

    /**
     * @When I upload a lay CSV that has 1 row with an invalid report type and :newEntitiesCount valid row
     */
    public function iUploadCsvWithInvalidReportTypeAndValidRows(int $newEntitiesCount)
    {
        $this->iAmOnAdminLayCsvUploadPage();

        $this->preRegistration['expected'] = $newEntitiesCount;

        $filePath = 'sirius-csvs/lay-1-row-invalid-report-type-1-valid-row.csv';

        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload Lay users');
    }

    /**
     * @When I upload an org CSV that has a new named deputy in a new organisation for an existing client
     */
    public function iUploadCsvThatHasNewNamedDeputyAndOrgForExistingClient()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->createProfAdminNotStarted(null, 'david@byrne.com', '1919191t', '3636363t');

        $this->em->clear();

        $existingClient = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => '1919191t']);

        if (is_null($existingClient)) {
            throw new BehatException('Existing Client not found with case number "1919191t"');
        }

        if (is_null($existingClient->getNamedDeputy())) {
            throw new BehatException('Existing client has no associated Named Deputy');
        }

        if (is_null($existingClient->getOrganisation())) {
            throw new BehatException('Existing client has no associated Organisation');
        }

        $this->clientBeforeCsvUpload = $existingClient;

        $filePath = 'sirius-csvs/org-1-row-new-named-deputy-and-org-existing-client.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();

        $this->clientAfterCsvUpload = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][0]]);

        if (is_null($this->clientAfterCsvUpload)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $this->entityUids['client_case_numbers'][0]));
        }
    }

    /**
     * @Then the named deputy associated with the client should be updated to the new named deputy
     */
    public function namedDeputyAssociatedWitClientShouldBeUpdatedToNewNamedDeputy()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $namedDeputyAfterUpload = $this->clientAfterCsvUpload->getNamedDeputy();

        if (is_null($namedDeputyAfterUpload)) {
            throw new BehatException('A named deputy is not associated with client after CSV upload');
        }

        $deputyUid = $this->entityUids['named_deputy_uids'][0];

        $namedDeputyWithCsvDeputyUid = $this->em
            ->getRepository(NamedDeputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if (is_null($namedDeputyWithCsvDeputyUid)) {
            throw new BehatException(sprintf('Named deputy with deputy uid "%s" not found', $deputyUid));
        }

        $this->assertEntitiesAreTheSame(
            $namedDeputyWithCsvDeputyUid,
            $namedDeputyAfterUpload,
            'Comparing named deputy with deputy no from CSV against named deputy associated with client after CSV upload'
        );
    }

    /**
     * @Then the organisation associated with the client should remain the same
     */
    public function organisationAssociatedWitClientShouldRemainTheSame()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->em->clear();

        $organisationAfterCsvUpload = $this->clientAfterCsvUpload->getOrganisation();

        if (is_null($organisationAfterCsvUpload)) {
            throw new BehatException('An organisation is not associated with client after CSV upload');
        }

        $this->assertEntitiesAreTheSame(
            $this->clientBeforeCsvUpload->getOrganisation(),
            $organisationAfterCsvUpload,
            'Comparing organisation associated with client before CSV upload against organisation associated with client after CSV upload'
        );
    }

    /**
     * @Then the organisation associated with the client should be updated to the new organisation
     */
    public function organisationAssociatedWitClientShouldBeUpdatedToNewOrganisation()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $newOrganisation = $this->clientAfterCsvUpload->getOrganisation();

        if (is_null($newOrganisation)) {
            throw new BehatException('An organisation is not associated with client after CSV upload');
        }

        $this->assertStringEqualsString(
            $this->entityUids['org_email_identifiers'][0],
            $newOrganisation->getEmailIdentifier(),
            'Comparing organisation email identifier in CSV against organisation associated with client after CSV upload'
        );
    }

    /**
     * @Then a new report should be generated for the client
     */
    public function newReportGeneratedForClient()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $reportAfterCsvUpload = $this->clientAfterCsvUpload->getCurrentReport();

        if (is_null($reportAfterCsvUpload)) {
            throw new BehatException('A report is not associated with client after CSV upload');
        }

        $this->assertEntitiesAreNotTheSame(
            $this->clientBeforeCsvUpload->getCurrentReport(),
            $reportAfterCsvUpload,
            'Comparing report associated with client before CSV upload against report associated with client after CSV upload'
        );
    }

    /**
     * @When I upload an org CSV that contains a new org email and street address but the same deputy number for an existing clients named deputy
     */
    public function iUploadCsvThatHasOrgEmailAndStreetAddressButSameDepNoForExistingClient()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->createProfAdminNotStarted(null, 'sufjan@stevens.com', '2828282t', '20082008');

        $this->em->clear();

        $existingClient = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => '2828282t']);

        if (is_null($existingClient)) {
            throw new BehatException('Existing Client not found with case number "2828282t"');
        }

        $this->clientBeforeCsvUpload = $existingClient;

        $filePath = 'sirius-csvs/org-1-row-existing-named-deputy-and-client-new-org-and-street-address.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();

        $this->clientAfterCsvUpload = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][0], 'deletedAt' => null]);

        if (is_null($this->clientAfterCsvUpload)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $this->entityUids['client_case_numbers'][0]));
        }
    }

    /**
     * @When I upload an org CSV that has a an existing case number and new made date for an existing client
     */
    public function iUploadCsvThatHasExistingCaseNumberNewMadeDateForExistingClient()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->createProfAdminNotStarted(null, 'sufjan@stevens.com', '16431643');

        $this->em->clear();

        $existingClient = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => '16431643']);

        if (is_null($existingClient)) {
            throw new BehatException('Existing Client not found with case number "16431643"');
        }

        $this->clientBeforeCsvUpload = $existingClient;

        $filePath = 'sirius-csvs/org-1-updated-row-existing-case-number-new-made-date.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();

        $this->clientAfterCsvUpload = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][0], 'deletedAt' => null]);

        if (is_null($this->clientAfterCsvUpload)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $this->entityUids['client_case_numbers'][0]));
        }
    }

    /**
     * @Then the named deputy's address should be updated to :address
     */
    public function theNamedDeputiesAddressShouldBeUpdatedTo(string $address)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $namedDeputyAfterCsvUpload = $this->clientAfterCsvUpload->getNamedDeputy();

        if (is_null($namedDeputyAfterCsvUpload)) {
            throw new BehatException('A named deputy is not associated with client after CSV upload');
        }

        $actualNamedDeputiesAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $namedDeputyAfterCsvUpload->getAddress1(),
            $namedDeputyAfterCsvUpload->getAddress2(),
            $namedDeputyAfterCsvUpload->getAddress3(),
            $namedDeputyAfterCsvUpload->getAddress4(),
            $namedDeputyAfterCsvUpload->getAddress5(),
            $namedDeputyAfterCsvUpload->getAddressPostcode()
        );

        $this->assertStringEqualsString(
            $address,
            $actualNamedDeputiesAddress,
            'Comparing named deputy address associated with client after CSV upload against step address'
        );
    }

    /**
     * @Then the named deputy associated with the client should remain the same
     */
    public function namedDeputyAssociatedWitClientShouldRemainTheSame()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->em->clear();

        $namedDeputyAfterCsvUpload = $this->clientAfterCsvUpload->getNamedDeputy();

        if (is_null($namedDeputyAfterCsvUpload)) {
            throw new BehatException('A named deputy is not associated with client after CSV upload');
        }

        $this->assertEntitiesAreTheSame(
            $this->clientBeforeCsvUpload->getNamedDeputy(),
            $namedDeputyAfterCsvUpload,
            'Comparing named deputy associated with client before CSV upload against named deputy associated with client after CSV upload'
        );
    }

    /**
     * @Then the report associated with the client should remain the same
     */
    public function reportAssociatedWithClientShouldRemainTheSame()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $reportAfterCsvUpload = $this->clientAfterCsvUpload->getCurrentReport();

        if (is_null($reportAfterCsvUpload)) {
            throw new BehatException('A report is not associated with client after CSV upload');
        }

        $this->assertEntitiesAreTheSame(
            $this->clientBeforeCsvUpload->getCurrentReport(),
            $reportAfterCsvUpload,
            'Comparing report associated with client before CSV upload against report associated with client after CSV upload'
        );
    }

    /**
     * @When I upload an org CSV that contains two rows with the same named deputy at two different addresses with different deputy uids
     */
    public function iUploadCsvWithOneNamedDeputyOnTwoLinesWithDifferentAddresses()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $filePath = 'sirius-csvs/org-2-rows-1-named-deputy-with-different-addresses.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();
    }

    /**
     * @Then there should be two named deputies created
     */
    public function shouldBeTwoNamedDeputiesWithSeparateAddresses()
    {
        $client1 = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][0]]);

        if (is_null($client1)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $this->entityUids['client_case_numbers'][0]));
        }

        $client2 = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][1]]);

        if (is_null($client2)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $this->entityUids['client_case_numbers'][1]));
        }

        $this->assertEntitiesAreNotTheSame(
            $client1->getNamedDeputy(),
            $client2->getNamedDeputy(),
            'Comparing named deputies of clients created during CSV upload'
        );
    }

    /**
     * @Then the named deputy for case number :caseNumber should have the address :fullAddress
     */
    public function namedDeputyForCaseNumberShouldHaveAddress(string $caseNumber, string $fullAddress)
    {
        $client = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $caseNumber]);

        if (is_null($client)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $caseNumber));
        }

        $actualNamedDeputiesAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $client->getNamedDeputy()->getAddress1(),
            $client->getNamedDeputy()->getAddress2(),
            $client->getNamedDeputy()->getAddress3(),
            $client->getNamedDeputy()->getAddress4(),
            $client->getNamedDeputy()->getAddress5(),
            $client->getNamedDeputy()->getAddressPostcode()
        );

        $this->assertStringEqualsString(
            $fullAddress,
            $actualNamedDeputiesAddress,
            'Comparing address defined in step against actual named deputy address'
        );
    }

    /**
     * @Given I upload an org CSV that has an organisation name :name but missing deputy first and last name
     */
    public function iUploadAnOrgCSVThatHasAnOrganisationNameButMissingDeputyFirstAndLastName($name)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $filePath = 'sirius-csvs/org-1-row-1-named-deputy-with-org-name-no-first-last-name.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();
    }

    /**
     * @Then the named deputy :firstOrLast name should be :expectedName
     */
    public function theNamedDeputyNameShouldBe($firstOrLast, $expectedName)
    {
        $expectedName = 'empty' === $expectedName ? '' : $expectedName;

        $namedDeputyUid = $this->entityUids['named_deputy_uids'][0];

        $namedDeputy = $this->em
            ->getRepository(NamedDeputy::class)
            ->findOneBy(['deputyUid' => $namedDeputyUid]);

        if (is_null($namedDeputy)) {
            throw new BehatException(sprintf('Could not find a named deputy with UID "%s"', $namedDeputyUid));
        }

        switch ($firstOrLast) {
            case 'first':
                $actualName = $namedDeputy->getFirstname();
                break;
            case 'last':
                $actualName = $namedDeputy->getLastname();
                break;
            default:
                throw new BehatException(sprintf('Can only match on firstName or lastName, "%s" provided.', $firstOrLast));
        }

        $matched = $actualName === $expectedName;

        if (!$matched) {
            throw new BehatException(sprintf('The named deputy "%s" name did not match. Wanted "%s", got "%s".', $firstOrLast, $expectedName, $actualName));
        }
    }

    /**
     * @Given I upload an org CSV that has one person deputy and one organisation deputy
     */
    public function iUploadAnOrgCSVThatHasOnePersonDeputyAndOneOrganisationDeputy()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $filePath = 'sirius-csvs/org-2-rows-1-person-deputy-1-org-deputy.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->clients['expected'] = 2;
        $this->namedDeputies['expected'] = 2;
        $this->organisations['expected'] = 2;
        $this->reports['expected'] = 2;

        $this->em->clear();
    }

    /**
     * @Given I upload an org CSV that updates the person deputy with an org name and the org deputy with a person name
     */
    public function iUploadAnOrgCSVThatUpdatesThePersonDeputyWithAnOrgNameAndTheOrgDeputyWithAPersonName()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $filePath = 'sirius-csvs/org-2-rows-1-person-deputy-1-org-deputy-updated-names.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();
    }

    /**
     * @Given I upload an org CSV that updates the deputy's email
     */
    public function iUploadAnOrgCSVThatUpdatesTheDeputysEmail()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $filePath = 'sirius-csvs/org-2-rows-1-person-deputy-1-org-deputy-updated-emails.csv';
        $this->uploadCsvAndCountCreatedEntities($filePath, 'Upload PA/Prof users');

        $this->em->clear();
    }

    /**
     * @Then the named deputy with deputy UID :deputyUid should have the full name :fullName
     */
    public function theNamedDeputyWithDeputyUIDShouldHaveTheFullName($deputyUid, $fullName)
    {
        $namedDeputy = $this->em
            ->getRepository(NamedDeputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if (is_null($namedDeputy)) {
            throw new BehatException(sprintf('Could not find a named deputy with UID "%s"', $deputyUid));
        }

        if (!empty($namedDeputy->getLastname())) {
            $actualName = sprintf('%s %s', $namedDeputy->getFirstname(), $namedDeputy->getLastname());
        } else {
            $actualName = $namedDeputy->getFirstname();
        }

        $nameMatches = $actualName === $fullName;

        if (!$nameMatches) {
            throw new BehatException(sprintf('The deputies name was not updated. Wanted: "%s", got "%s"', $fullName, $actualName));
        }
    }

    /**
     * @Then the named deputy with deputy UID :deputyUid should have the email :email
     */
    public function theNamedDeputyWithDeputyUIDShouldHaveTheEmail($deputyUid, $email)
    {
        $namedDeputy = $this->em
            ->getRepository(NamedDeputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if (is_null($namedDeputy)) {
            throw new BehatException(sprintf('Could not find a named deputy with UID "%s"', $deputyUid));
        }

        $actualEmail = $namedDeputy->getEmail1();

        $emailMatches = $actualEmail === $email;

        if (!$emailMatches) {
            throw new BehatException(sprintf("The deputy's email was not updated. Wanted: '%s', got '%s'", $email, $actualEmail));
        }
    }
}
