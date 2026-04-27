<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Registration;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Behat\BehatException;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Console\Input\ArrayInput;

trait IngestTrait
{
    private array $clients = [
        'added' => ['expected' => 0, 'found' => 0],
        'updated' => ['expected' => 0, 'found' => 0],
    ];
    private array $deputies = [
        'added' => ['expected' => 0, 'found' => 0],
        'updated' => ['expected' => 0, 'found' => 0],
    ];
    private array $organisations = [
        'added' => ['expected' => 0, 'found' => 0],
        'updated' => ['expected' => 0, 'found' => 0],
    ];
    private array $reports = [
        'added' => ['expected' => 0, 'found' => 0],
        'updated' => ['expected' => 0, 'found' => 0],
    ];

    private array $errors = [
        'count' => 0,
        'messages' => [],
    ];

    private array $preRegistration = ['expected' => 0, 'found' => 0];

    private array $skipped = ['expected' => 0, 'found' => 0];
    private array $expectedMissingDTOProperties = [];
    public array $entityUids = [
        'client_case_numbers' => [],
        'deputy_uids' => [],
        'org_email_identifiers' => [],
        'sirius_case_numbers' => [],
    ];

    private string $expectedDeputyName = '';
    private string $expectedDeputyAddress = '';
    private string $expectedReportType = '';
    private string $expectedCaseNumberAssociatedWithError = '';
    private string $expectedUnexpectedColumn = '';
    private string $csvFileName = '';

    private Client $clientBeforeCsvUpload;
    private Client $clientAfterCsvUpload;

    /**
     * @Given a csv has been uploaded to the sirius bucket with the file :fileName
     */
    public function aCsvHasBeenUploadedTheSiriusBucketWithTheFile(string $fileName): void
    {
        $this->visitFrontendPath($this->getClientLoginPageUrl());

        $this->csvFileName = $fileName;
        $filePath = sprintf('%s/fixtures/sirius-csvs/%s', dirname(__DIR__, 3), $this->csvFileName);
        $fileBody = file_get_contents($filePath);

        if (!$fileBody) {
            throw new BehatException("CSV file $fileName was not readable");
        }

        $this->s3->store($this->csvFileName, $fileBody);

        unset($fileBody);
    }

    /**
     * @When I run the lay CSV command the file contains the following new entities:
     */
    public function iUploadAnOrgCsvThatContainsTheFollowingNewEntities(TableNode $table): void
    {
        $hash = $table->getHash();

        if (count($hash) > 1) {
            throw new BehatException('Only a single row of entity numbers is supported. Remove additional rows from the test.');
        }

        $this->clients['added']['expected'] = intval($hash[0]['clients']);
        $this->deputies['added']['expected'] = intval($hash[0]['deputies']);
        $this->organisations['added']['expected'] = intval($hash[0]['organisations']);
        $this->reports['added']['expected'] = intval($hash[0]['reports']);

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @Then the new :type entities should be added to the database
     */
    public function theNewEntitiesShouldBeAddedToTheDatabase(string $type): void
    {
        if (in_array(strtolower($type), ['org', 'pa'])) {
            $this->assertIntEqualsInt(
                $this->clients['added']['expected'],
                $this->clients['added']['found'],
                'Count of entities based on UIDs - clients'
            );
            $this->assertIntEqualsInt(
                $this->deputies['added']['expected'],
                $this->deputies['added']['found'],
                'Count of entities based on UIDs - deputies'
            );
            $this->assertIntEqualsInt(
                $this->organisations['added']['expected'],
                $this->organisations['added']['found'],
                'Count of entities based on UIDs - organisations'
            );
            $this->assertIntEqualsInt(
                $this->reports['added']['expected'],
                $this->reports['added']['found'],
                'Count of entities based on UIDs - reports'
            );
        } else {
            $this->assertIntEqualsInt(
                $this->preRegistration['expected'],
                $this->preRegistration['found'],
                'Count of entities based on UIDs - Pre-registration'
            );
        }
    }

    /**
     * @Then the count of the new :type entities added should be in the command output
     */
    public function theNewEntitiesCountShouldBeInTheCommandOutput(string $type): void
    {
        $output = $this->output->fetch();
        if (in_array(strtolower($type), ['org', 'pa'])) {
            $processedStats = [
                [
                    'added' => [
                        'dataType' => sprintf('clients added: %u', $this->clients['added']['expected']),
                        'message' => 'Asserting Org Clients added on the Command output is incorrect',
                    ],
                    'updated' => [
                        'dataType' => sprintf('clients updated: %u', $this->clients['updated']['expected']),
                        'message' => 'Asserting Org Clients updated on the Command output is incorrect',
                    ],
                ],
                [
                    'added' => [
                        'dataType' => sprintf('deputies added: %u', $this->deputies['added']['expected']),
                        'message' => 'Asserting Org Deputies added on the Command output is incorrect',
                    ],
                    'updated' => [
                        'dataType' => sprintf('deputies updated: %u', $this->deputies['updated']['expected']),
                        'message' => 'Asserting Org Deputies updated on the Command output is incorrect',
                    ],
                ],
                [
                    'added' => [
                        'dataType' => sprintf('organisations added: %u', $this->organisations['added']['expected']),
                        'message' => 'Asserting Organisations added on the Command output is incorrect',
                    ],
                    'updated' => [
                        'dataType' => sprintf('organisations updated: %u', $this->organisations['updated']['expected']),
                        'message' => 'Asserting Organisations updated on the Command output is incorrect',
                    ],
                ],
                [
                    'added' => [
                        'dataType' => sprintf('reports added: %u', $this->reports['added']['expected']),
                        'message' => 'Asserting Reports added on the Command output is incorrect',
                    ],
                    'updated' => [
                        'dataType' => sprintf('reports updated: %u', $this->reports['updated']['expected']),
                        'message' => 'Asserting Reports updated on the Command output is incorrect',
                    ],
                ],
            ];

            foreach ($processedStats as $type) {
                $this->assertStringContainsString(
                    $type['added']['dataType'],
                    $output,
                    $type['added']['message']
                );

                $this->assertStringContainsString(
                    $type['updated']['dataType'],
                    $output,
                    $type['updated']['message']
                );
            }

            if ($this->errors['count'] >= 1) {
                $this->assertStringContainsString(
                    (string) $this->errors['count'],
                    $output,
                    'Asserting expected amount of errors during ingestion is incorrect'
                );

                $this->assertStringContainsString(
                    implode(', ', $this->errors['messages']),
                    $output,
                    'Asserting expected error messages are present on the Command output is incorrect'
                );
            }
        } else {
            $this->assertStringContainsString(
                sprintf('%u added.', $this->preRegistration['expected']),
                $output,
                'Asserting users added to pre-registration table via Command is incorrect'
            );
        }

        $this->assertStringContainsString(
            sprintf('%u skipped.', $this->skipped['expected']),
            $output,
            'Asserting users that were skipped via Command is incorrect'
        );
    }

    private function extractUidsFromCsv(string $csvFilePath): void
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $csvFilePath;
            if (is_file($fullPath)) {
                $csvFilePath = $fullPath;
            }
        }

        $fileContents = file($csvFilePath);
        $csvRows = [];
        foreach ($fileContents as $row) {
            $csvRows[] = str_getcsv($row, ',', '"', '');
        }
        unset($fileContents);

        array_walk($csvRows, function (&$a) use ($csvRows): void {
            $a = array_combine($csvRows[0], $a);
        });
        array_shift($csvRows); // remove column header

        foreach ($csvRows as $row) {
            $email = empty($row['DeputyEmail']) ? null : substr(strstr($row['DeputyEmail'], '@'), 1);

            $this->entityUids['client_case_numbers'][] = $row['Case'] ?? null;
            $this->entityUids['sirius_case_numbers'][] = $row['Case'] ?? '';
            $this->entityUids['deputy_uids'][] = $row['DeputyUid'] ?? '';
            $this->entityUids['org_email_identifiers'][] = $email;
        }

        $this->entityUids['client_case_numbers'] = array_unique($this->entityUids['client_case_numbers']);
        $this->entityUids['sirius_case_numbers'] = array_unique($this->entityUids['sirius_case_numbers']);
        $this->entityUids['deputy_uids'] = array_unique($this->entityUids['deputy_uids']);
        $this->entityUids['org_email_identifiers'] = array_unique($this->entityUids['org_email_identifiers']);

        unset($csvRows);
    }

    private function countCreatedEntities(): void
    {
        $this->em->clear();

        $clients = $this->em->getRepository(Client::class)->findBy(['caseNumber' => $this->entityUids['client_case_numbers']]);

        $numClients = 0;
        $numReports = 0;
        foreach ($clients as $client) {
            ++$numClients;
            $numReports += count($client->getReports());
        }
        $this->clients['added']['found'] = $numClients;
        $this->reports['added']['found'] = $numReports;

        unset($clients);

        $deputies = $this->em->getRepository(Deputy::class)->findBy(['deputyUid' => $this->entityUids['deputy_uids']]);
        $this->deputies['added']['found'] = count($deputies);
        unset($deputies);

        $orgs = $this->em->getRepository(Organisation::class)->findBy(['emailIdentifier' => $this->entityUids['org_email_identifiers']]);
        $this->organisations['added']['found'] = count($orgs);
        unset($orgs);

        $preRegistrations = $this->em->getRepository(PreRegistration::class)->findBy(['caseNumber' => $this->entityUids['sirius_case_numbers']]);
        $this->preRegistration['found'] = count($preRegistrations);
        unset($preRegistrations);
    }

    /**
     * @When I run the lay CSV command the file has a new named deputy :newDeputy within the same org as the clients existing name deputy
     */
    public function iUploadAnOrgCsvThatHasANewMadeDateAndDeputyWithinTheSameOrgAsTheClientsExistingDeputy(string $newDeputy): void
    {
        $this->expectedDeputyName = $newDeputy;

        $this->deputies['added']['expected'] = 1;
        $this->clients['updated']['expected'] = 1;
        $this->reports['updated']['expected'] = 1;

        $this->createProfAdminNotStarted(null, 'professor@mccracken4.com', '40000000');

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    private function uploadCsvAndCountCreatedEntities(string $fileName): void
    {
        $filePath = sprintf('sirius-csvs/%s', $fileName);
        $this->extractUidsFromCsv($filePath);

        $type = (str_starts_with($fileName, 'lay-')) ? 'lay' : 'org';

        $this->runCSVCommand($type, $fileName);
        $this->countCreatedEntities();
    }

    /**
     * @Then the clients named deputy should be updated
     */
    public function theClientsDeputyShouldBeUpdated(): void
    {
        $this->em->clear();
        $client = $this->em->getRepository(Client::class)->find($this->profAdminDeputyHealthWelfareNotStartedDetails->getClientId());

        $this->assertStringEqualsString(
            $this->expectedDeputyName,
            sprintf('%s %s', $client->getDeputy()->getFirstName(), $client->getDeputy()->getLastName()),
            'Comparing expected deputy full name to client deputy full name'
        );
    }

    /**
     * @When I run the lay CSV command the file has a new address :address for an existing named deputy
     */
    public function iUploadACsvThatHasANewAddressAndPhoneDetailsForAnExistingDeputy(string $address): void
    {
        $this->deputies['added']['expected'] = 1;
        $this->clients['updated']['expected'] = 1;
        $this->reports['updated']['expected'] = 1;

        $this->expectedDeputyAddress = $address;

        $this->createProfAdminNotStarted(null, 'him@jojo5.com', '50000000', '66648');

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @Then the named deputy's address should be updated
     */
    public function theDeputiesAddressShouldBeUpdated(): void
    {
        $this->em->clear();

        $deputy = $this->em
            ->getRepository(Client::class)
            ->find($this->profAdminDeputyHealthWelfareNotStartedDetails->getClientId())
            ->getDeputy();

        $actualDeputiesAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $deputy->getAddress1(),
            $deputy->getAddress2(),
            $deputy->getAddress3(),
            $deputy->getAddress4(),
            $deputy->getAddress5(),
            $deputy->getAddressPostcode()
        );

        $this->assertStringEqualsString(
            $this->expectedDeputyAddress,
            $actualDeputiesAddress,
            'Comparing expected deputy address to actual deputy address'
        );
    }

    /**
     * @When I run the lay CSV command the file has a new report type :reportTypeNumber for an existing report that has not been submitted or unsubmitted
     */
    public function iUploadACsvThatHasANewReportType(string $reportTypeNumber): void
    {
        $this->expectedReportType = $reportTypeNumber;

        $this->deputies['updated']['expected'] = 1;
        $this->reports['updated']['expected'] = 1;

        $this->createProfAdminNotStarted(null, 'fuzzy.lumpkins@jojo6.com', '60000000', '740000000001');

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @When I run the lay CSV command the file has a new report type :reportTypeNumber for a dual case
     */
    public function iUploadACsvThatHasANewReportTypeForDualCase(string $reportTypeNumber): void
    {
        $this->expectedReportType = $reportTypeNumber;

        $this->deputies['added']['expected'] = 1;
        $this->organisations['added']['expected'] = 1;
        $this->deputies['updated']['expected'] = 1;

        $this->createProfAdminNotStarted(null, 'fuzzy.lumpkins@jojo6.com', '60000001', '750000000002');

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @Then the report type should be updated
     */
    public function theReportTypeShouldBeUpdated(): void
    {
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
     * @When I run the lay CSV command the file has 1 row with missing values 'LastReportDay, MadeDate, DeputyEmail' for case number ':caseNumber' and 1 valid row
     */
    public function iUploadACsvThatHasMissingValueAndOneValidRow(string $caseNumber): void
    {
        $this->clients['added']['expected'] = 1;
        $this->organisations['added']['expected'] = 1;
        $this->deputies['added']['expected'] = 1;
        $this->reports['added']['expected'] = 1;
        $this->errors['count'] = 1;
        $this->errors['messages'][] = "Error for case $caseNumber: Missing data to upload row: LastReportDay, MadeDate, DeputyEmail";

        $this->createProfAdminNotStarted();

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @Given I attempt to upload a :deputyRole CSV
     */
    public function iAttemptToUploadACSV(string $deputyRole): void
    {
        $this->selectOption('form[type]', $deputyRole);
        $this->pressButton('Continue');
    }

    /**
     * @When I run the lay CSV command for :csvFilename
     */
    public function iRunTheLayCsvCommandFor(string $csvFilename): void
    {
        $this->uploadCsvAndCountCreatedEntities($csvFilename);
    }

    /**
     * @When I run the lay CSV command the file contains :newEntitiesCount new pre-registration entities
     */
    public function iRunTheLayCsvCommandTheFileContainsNPreRegistrationEntities(int $newEntitiesCount): void
    {
        $this->preRegistration['expected'] = $newEntitiesCount;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @When I run the lay CSV command the file contains a new pre-registration entity with special characters
     */
    public function iRunTheLayCsvCommandTheFileContainsANewPreRegistrationEntityWithSpecialCharacters(): void
    {
        $this->preRegistration['expected'] = 1;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @When I run the lay CSV command where a file has a new report type :reportTypeNumber for case number :caseNumber
     */
    public function iRunTheLayCsvCommandWhereAFileHasANewReportTypeForCase(string $reportTypeNumber, string $caseNumber): void
    {
        $this->expectedReportType = $reportTypeNumber;

        $this->createPfaHighNotStarted(null, $caseNumber);

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @Then the clients report type should be updated
     */
    public function theClientsReportTypeShouldBeUpdated(): void
    {
        $this->em->clear();
        $client = $this->em->getRepository(Client::class)->find($this->layDeputyNotStartedPfaHighAssetsDetails->getClientId());

        $this->assertStringEqualsString(
            $this->expectedReportType,
            $client->getCurrentReport()->getType(),
            'Comparing expected report type to clients report type'
        );
    }

    /**
     * @When I run the lay CSV command the file has :entitiesSkipped row with missing values for 'caseNumber, clientLastname, deputyUid and deputySurname' and :newEntitiesCount valid row
     */
    public function iUploadCsvWith1ValidAnd1InvalidRow(int $entitiesSkipped, int $newEntitiesCount): void
    {
        $this->expectedMissingDTOProperties = ['caseNumber', 'clientLastname', 'deputyUid', 'deputySurname'];
        $this->preRegistration['expected'] = $newEntitiesCount;
        $this->skipped['expected'] = $entitiesSkipped;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @When I run the lay CSV command the file has :entitiesSkipped row with an invalid report type and :newEntitiesCount valid row
     */
    public function iUploadCsvWithInvalidReportTypeAndValidRows(int $entitiesSkipped, int $newEntitiesCount): void
    {
        $this->preRegistration['expected'] = $newEntitiesCount;
        $this->skipped['expected'] = $entitiesSkipped;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @When I run the lay CSV command the file has a new named deputy in a new organisation for an existing client
     */
    public function iUploadCsvThatHasNewDeputyAndOrgForExistingClient(): void
    {
        $this->deputies['added']['expected'] = 1;
        $this->organisations['added']['expected'] = 1;
        $this->clients['updated']['expected'] = 1;
        $this->reports['updated']['expected'] = 1;

        $this->createProfAdminNotStarted(null, 'david@byrne.com', '1919191t', '3636363t');

        $this->em->clear();

        $existingClient = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => '1919191t']);

        if (is_null($existingClient)) {
            throw new BehatException('Existing Client not found with case number "1919191t"');
        }

        if (is_null($existingClient->getDeputy())) {
            throw new BehatException('Existing client has no associated Deputy');
        }

        if (is_null($existingClient->getOrganisation())) {
            throw new BehatException('Existing client has no associated Organisation');
        }

        $this->clientBeforeCsvUpload = $existingClient;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();

        $this->clientAfterCsvUpload = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][0]]);
    }

    /**
     * @Then the organisation associated with the client should remain the same
     */
    public function organisationAssociatedWitClientShouldRemainTheSame(): void
    {
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
    public function organisationAssociatedWitClientShouldBeUpdatedToNewOrganisation(): void
    {
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
     * @When I run the lay CSV command the file contains a new org email and street address but the same deputy number for an existing clients named deputy
     */
    public function iUploadCsvThatHasOrgEmailAndStreetAddressButSameDepNoForExistingClient(): void
    {
        $this->organisations['added']['expected'] = 1;
        $this->clients['updated']['expected'] = 1;
        $this->deputies['updated']['expected'] = 1;
        $this->reports['updated']['expected'] = 1;

        $this->createProfAdminNotStarted(null, 'sufjan@stevens.com', '2828282t', '20082008');

        $this->em->clear();

        $existingClient = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => '2828282t']);

        if (is_null($existingClient)) {
            throw new BehatException('Existing Client not found with case number "2828282t"');
        }

        $this->clientBeforeCsvUpload = $existingClient;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();

        $this->clientAfterCsvUpload = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $this->entityUids['client_case_numbers'][0], 'deletedAt' => null]);
    }

    /**
     * @Then the named deputy's address should be updated to :address
     */
    public function theDeputiesAddressShouldBeUpdatedTo(string $address): void
    {
        $DeputyAfterCsvUpload = $this->clientAfterCsvUpload->getDeputy();

        if (is_null($DeputyAfterCsvUpload)) {
            throw new BehatException('A deputy is not associated with client after CSV upload');
        }

        $actualDeputiesAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $DeputyAfterCsvUpload->getAddress1(),
            $DeputyAfterCsvUpload->getAddress2(),
            $DeputyAfterCsvUpload->getAddress3(),
            $DeputyAfterCsvUpload->getAddress4(),
            $DeputyAfterCsvUpload->getAddress5(),
            $DeputyAfterCsvUpload->getAddressPostcode()
        );

        $this->assertStringEqualsString(
            $address,
            $actualDeputiesAddress,
            'Comparing deputy address associated with client after CSV upload against step address'
        );
    }

    /**
     * @Then the named deputy associated with the client should remain the same
     */
    public function deputyAssociatedWitClientShouldRemainTheSame(): void
    {
        $this->em->clear();

        $deputyAfterCsvUpload = $this->clientAfterCsvUpload->getDeputy();

        if (is_null($deputyAfterCsvUpload)) {
            throw new BehatException('A deputy is not associated with client after CSV upload');
        }

        $this->assertEntitiesAreTheSame(
            $this->clientBeforeCsvUpload->getDeputy(),
            $deputyAfterCsvUpload,
            'Comparing deputy associated with client before CSV upload against deputy associated with client after CSV upload'
        );
    }

    /**
     * @Then the report associated with the client should remain the same
     */
    public function reportAssociatedWithClientShouldRemainTheSame(): void
    {
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
     * @When I run the lay CSV command the file contains two rows with the same named deputy at two different addresses with different deputy uids
     */
    public function iUploadCsvWithOneDeputyOnTwoLinesWithDifferentAddresses(): void
    {
        $this->clients['added']['expected'] = 2;
        $this->deputies['added']['expected'] = 2;
        $this->reports['added']['expected'] = 2;
        $this->organisations['added']['expected'] = 1;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();
    }

    /**
     * @Then there should be two named deputies created
     */
    public function shouldBeTwoDeputiesWithSeparateAddresses(): void
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
            $client1->getDeputy(),
            $client2->getDeputy(),
            'Comparing deputies of clients created during CSV upload'
        );
    }

    /**
     * @Then the named deputy for case number :caseNumber should have the address :fullAddress
     */
    public function deputyForCaseNumberShouldHaveAddress(string $caseNumber, string $fullAddress): void
    {
        $client = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $caseNumber]);

        if (is_null($client)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $caseNumber));
        }

        $actualDeputiesAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $client->getDeputy()->getAddress1(),
            $client->getDeputy()->getAddress2(),
            $client->getDeputy()->getAddress3(),
            $client->getDeputy()->getAddress4(),
            $client->getDeputy()->getAddress5(),
            $client->getDeputy()->getAddressPostcode()
        );

        $this->assertStringEqualsString(
            $fullAddress,
            $actualDeputiesAddress,
            'Comparing address defined in step against actual deputy address'
        );
    }

    /**
     * @Given I run the lay CSV command the file has an organisation name but is missing deputy first and last names
     */
    public function iUploadAnOrgCSVThatHasAnOrganisationNameButMissingDeputyFirstAndLastName(): void
    {
        $this->clients['added']['expected'] = 1;
        $this->deputies['added']['expected'] = 1;
        $this->reports['added']['expected'] = 1;
        $this->organisations['added']['expected'] = 1;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();
    }

    /**
     * @Then the named deputy :firstOrLast name should be :expectedName
     */
    public function theDeputyNameShouldBe(string $firstOrLast, string $expectedName): void
    {
        $expectedName = 'empty' === $expectedName ? '' : $expectedName;

        $deputyUid = $this->entityUids['deputy_uids'][0];

        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if (is_null($deputy)) {
            throw new BehatException(sprintf('Could not find a deputy with UID "%s"', $deputyUid));
        }

        switch ($firstOrLast) {
            case 'first':
                $actualName = $deputy->getFirstname();
                break;
            case 'last':
                $actualName = $deputy->getLastname();
                break;
            default:
                throw new BehatException(sprintf('Can only match on firstName or lastName, "%s" provided.', $firstOrLast));
        }

        $matched = $actualName === $expectedName;

        if (!$matched) {
            throw new BehatException(sprintf('The deputy "%s" name did not match. Wanted "%s", got "%s".', $firstOrLast, $expectedName, $actualName));
        }
    }

    /**
     * @Given I run the lay CSV command the file has one person deputy and one organisation deputy
     */
    public function iUploadAnOrgCSVThatHasOnePersonDeputyAndOneOrganisationDeputy(): void
    {
        $this->organisations['added']['expected'] = 2;
        $this->clients['added']['expected'] = 2;
        $this->deputies['added']['expected'] = 2;
        $this->reports['added']['expected'] = 2;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();
    }

    /**
     * @Given I run the lay CSV command the file has one person deputy and one organisation deputy 2nd run
     */
    public function iUploadAnOrgCSVThatHasOnePersonDeputyAndOneOrganisationDeputy2ndRun(): void
    {
        $this->organisations['added']['expected'] = 2;
        $this->clients['added']['expected'] = 2;
        $this->deputies['added']['expected'] = 2;
        $this->reports['added']['expected'] = 2;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();
    }

    /**
     * @Given I run the lay CSV command the file that updates the person deputy with an org name and the org deputy with a person name
     */
    public function iUploadAnOrgCSVThatUpdatesThePersonDeputyWithAnOrgNameAndTheOrgDeputyWithAPersonName(): void
    {
        $this->deputies['updated']['expected'] = 2;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();
    }

    /**
     * @Given I run the lay CSV command the file that updates the deputy's email
     */
    public function iUploadAnOrgCSVThatUpdatesTheDeputysEmail(): void
    {
        $this->organisations['added']['expected'] = 3;
        $this->clients['updated']['expected'] = 1;
        $this->deputies['updated']['expected'] = 1;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);

        $this->em->clear();
    }

    /**
     * @Then the named deputy with deputy UID :deputyUid should have the full name :fullName
     */
    public function theDeputyWithDeputyUIDShouldHaveTheFullName(string $deputyUid, string $fullName): void
    {
        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if (is_null($deputy)) {
            throw new BehatException(sprintf('Could not find a deputy with UID "%s"', $deputyUid));
        }

        if (!empty($deputy->getLastname())) {
            $actualName = sprintf('%s %s', $deputy->getFirstname(), $deputy->getLastname());
        } else {
            $actualName = $deputy->getFirstname();
        }

        $nameMatches = $actualName === $fullName;

        if (!$nameMatches) {
            throw new BehatException(sprintf('The deputies name was not updated. Wanted: "%s", got "%s"', $fullName, $actualName));
        }
    }

    /**
     * @Then the named deputy with deputy UID :deputyUid should have the email :email
     */
    public function theDeputyWithDeputyUIDShouldHaveTheEmail(string $deputyUid, string $email): void
    {
        $deputy = $this->em
            ->getRepository(Deputy::class)
            ->findOneBy(['deputyUid' => $deputyUid]);

        if (is_null($deputy)) {
            throw new BehatException(sprintf('Could not find a deputy with UID "%s"', $deputyUid));
        }

        $actualEmail = $deputy->getEmail1();

        $emailMatches = $actualEmail === $email;

        if (!$emailMatches) {
            throw new BehatException(sprintf("The deputy's email was not updated. Wanted: '%s', got '%s'", $email, $actualEmail));
        }
    }

    /**
     * @Given I run the lay CSV command the file contains :newEntitiesCount new pre-registration entities for the same case
     */
    public function iRunTheLayCsvCommandTheFileContainsNewPreRegistrationEntitesForTheSameCase(int $newEntitiesCount): void
    {
        $this->preRegistration['expected'] = $newEntitiesCount;

        $this->uploadCsvAndCountCreatedEntities($this->csvFileName);
    }

    /**
     * @Given the Lay deputy with deputy UID :deputyUid has :deputyClientCount associated active clients
     */
    public function theLayDeputyWithDeputyUidHasAssociatedClient($deputyUid, $deputyClientCount): void
    {
        $clientCount = $this->em
            ->getRepository(User::class)
            ->findActiveClientsCountForDeputyUid($deputyUid);

        if ($clientCount != $deputyClientCount) {
            throw new BehatException(sprintf("Unexpected number of active clients associated with this Deputy UID. Expected: '%s', got '%s'", $deputyClientCount, $clientCount));
        }
    }

    /**
     * @Then the client with case number :caseNumber should have the address :fullAddress
     */
    public function clientWithCaseNumberShouldHaveAddress(string $caseNumber, string $fullAddress): void
    {
        $client = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $caseNumber]);

        if (is_null($client)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $caseNumber));
        }

        $actualClientAddress = sprintf(
            '%s, %s, %s, %s, %s, %s',
            $client->getAddress() ?? '',
            $client->getAddress2() ?? '',
            $client->getAddress3() ?? '',
            $client->getAddress4() ?? '',
            $client->getAddress5() ?? '',
            $client->getPostcode() ?? ''
        );

        $this->assertStringEqualsString(
            $fullAddress,
            $actualClientAddress,
            'Comparing address defined in step against actual client address'
        );
    }

    /**
     * @Then the client with case number :caseNumber should have an active report with type :expectedReportType
     */
    public function clientWithCaseNumberShouldHaveReportType(string $caseNumber, string $expectedReportType): void
    {
        /* @var Client $client */
        $client = $this->em
            ->getRepository(Client::class)
            ->findOneBy(['caseNumber' => $caseNumber]);

        if (is_null($client)) {
            throw new BehatException(sprintf('Client not found with case number "%s"', $caseNumber));
        }

        $report = $client->getCurrentReport();

        $this->assertStringEqualsString(
            $report->getType(),
            $expectedReportType,
            'Comparing expected report type with actual report type'
        );
    }

    protected function runCSVCommand(string $type, string $fileName): void
    {
        $command = ('lay' === $type) ?
            'digideps:api:process-lay-csv' :
            'digideps:api:process-org-csv';

        $input = new ArrayInput([
            'command' => $command,
            'csv-filename' => $fileName,
        ]);

        $this->application->doRun($input, $this->output);
    }
}
