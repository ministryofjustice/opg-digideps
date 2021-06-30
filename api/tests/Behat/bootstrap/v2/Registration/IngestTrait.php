<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Tests\Behat\BehatException;
use Behat\Gherkin\Node\TableNode;
use DateTime;

trait IngestTrait
{
    private array $clients = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $namedDeputies = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $organisations = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $reports = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $expectedMissingDTOProperties = [];

    private ?DateTime $expectedClientCourtDate = null;

    private string $expectedNamedDeputyName = '';
    private string $expectedNamedDeputyAddress = '';
    private string $expectedReportType = '';
    private string $expectedCaseNumberAssociatedWithError = '';
    private string $expectedUnexpectedColumn = '';

    /**
     * @When I upload a :source CSV that contains the following new entities:
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

            $this->updateAllEntitiesCount('preUpdate');

            $this->selectOption('form[type]', 'org');
            $this->pressButton('Continue');

            $this->attachFileToField('admin_upload[file]', 'org-3-valid-rows.csv');
            $this->pressButton('Upload PA/Prof users');
            $this->waitForAjaxAndRefresh();
        } elseif ('sirius' === $source) {
            // Add Sirius steps
        } else {
            throw new BehatException('$source should be casrec or sirius');
        }
    }

    /**
     * @Then the new entities should be added to the database
     */
    public function theNewEntitiesShouldBeAddedToTheDatabase()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->updateAllEntitiesCount('postUpdate');

        $this->assertIntEqualsInt($this->clients['expected'], $this->clients['postUpdate'] - $this->clients['preUpdate'], 'Post update minus pre update entities count - clients');
        $this->assertIntEqualsInt($this->namedDeputies['expected'], $this->namedDeputies['postUpdate'] - $this->namedDeputies['preUpdate'], 'Post update minus pre update entities count - named deputies');
        $this->assertIntEqualsInt($this->organisations['expected'], $this->organisations['postUpdate'] - $this->organisations['preUpdate'], 'Post update minus pre update entities count - organisations');
        $this->assertIntEqualsInt($this->reports['expected'], $this->reports['postUpdate'] - $this->reports['preUpdate'], 'Post update minus pre update entities count - reports');
    }

    /**
     * @Then the count of the new entities added should be displayed on the page
     */
    public function theNewEntitiesCountShouldBeDisplayed()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->assertOnAlertMessage(sprintf('%s clients', $this->clients['expected']));
        $this->assertOnAlertMessage(sprintf('%s named deputies', $this->namedDeputies['expected']));
        $this->assertOnAlertMessage(sprintf('%s organisation', $this->organisations['expected']));
        $this->assertOnAlertMessage(sprintf('%s reports', $this->reports['expected']));
    }

    private function updateAllEntitiesCount(string $phase)
    {
        $this->clients[$phase] = $this->em->getRepository(Client::class)->countAllEntities();
        $this->namedDeputies[$phase] = $this->em->getRepository(NamedDeputy::class)->countAllEntities();
        $this->organisations[$phase] = $this->em->getRepository(Organisation::class)->countAllEntities();
        $this->reports[$phase] = $this->em->getRepository(Report::class)->countAllEntities();
    }

    /**
     * @When I upload a :source CSV that has a new made date :newMadeDate and named deputy :newNamedDeputy within the same org as the clients existing name deputy
     */
    public function iUploadACsvThatHasANewMadeDateAndNamedDeputyWithinTheSameOrgAsTheClientsExistingNameDeputy(string $source, string $newMadeDate, string $newNamedDeputy)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedClientCourtDate = new DateTime($newMadeDate);
        $this->expectedNamedDeputyName = $newNamedDeputy;

        $this->createProfAdminNotStarted('professor@mccracken4.com', '40000000');

        $this->attachFileToField('admin_upload[file]', 'org-1-updated-row-made-date-and-named-deputy.csv');
        $this->pressButton('Upload PA/Prof users');
        $this->waitForAjaxAndRefresh();
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
     * @When I upload a :source CSV that has a new address :address for an existing named deputy
     */
    public function iUploadACsvThatHasANewAddressAndPhoneDetailsForAnExistingNamedDeputy(string $source, string $address)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedNamedDeputyAddress = $address;

        $this->createProfAdminNotStarted('him@jojo5.com', '50000000', '66648');

        $this->attachFileToField('admin_upload[file]', 'org-1-updated-row-named-deputy-address.csv');
        $this->pressButton('Upload PA/Prof users');
        $this->waitForAjaxAndRefresh();
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
     * @When I upload a :source CSV that has a new report type :reportTypeNumber for an existing report that has not been submitted or unsubmitted
     */
    public function iUploadACsvThatHasANewReportType(string $source, string $reportTypeNumber)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedReportType = $reportTypeNumber;

        $this->createProfAdminNotStarted('fuzzy.lumpkins@jojo6.com', '60000000', '112233');

        $this->attachFileToField('admin_upload[file]', 'org-1-updated-row-report-type.csv');
        $this->pressButton('Upload PA/Prof users');
        $this->waitForAjaxAndRefresh();
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
     * @When I upload a :source CSV that has 1 row with missing values 'Last Report Day, Made Date, Email' for case number :caseNumber and 1 valid row
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

        $this->updateAllEntitiesCount('preUpdate');

        $this->attachFileToField('admin_upload[file]', 'org-1-row-missing-last-report-date-1-valid-row.csv');
        $this->pressButton('Upload PA/Prof users');
        $this->waitForAjaxAndRefresh();
    }

    /**
     * @Then I should see an error showing the problem
     */
    public function iShouldSeeErrorShowingProblem()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        foreach ($this->expectedMissingDTOProperties as $expectedMissingDTOProperty) {
            $this->assertOnErrorMessage($expectedMissingDTOProperty);
        }

        $this->assertOnErrorMessage($this->expectedCaseNumberAssociatedWithError);
    }

    /**
     * @When I upload a :source CSV that does not have any of the required columns
     */
    public function iUploadACsvThatHasMissingDeputyNoColumn(string $source)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->attachFileToField('admin_upload[file]', 'org-1-row-missing-all-required-columns.csv');
        $this->pressButton('Upload PA/Prof users');
        $this->waitForAjaxAndRefresh();
    }

    /**
     * @Then I should see an error showing which columns are missing
     */
    public function iShouldSeeErrorShowingMissingColumns()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

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

        foreach ($requiredColumns as $requiredColumn) {
            $this->assertOnErrorMessage($requiredColumn);
        }
    }

    /**
     * @When I upload a :source CSV that has an/a :columnName column
     */
    public function iUploadACsvThatHasNdrColumn(string $source, string $columnName)
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->expectedUnexpectedColumn = $columnName;

        $this->attachFileToField('admin_upload[file]', 'org-1-row-with-ndr-column.csv');
        $this->pressButton('Upload PA/Prof users');
        $this->waitForAjaxAndRefresh();
    }

    /**
     * @Then I should see an error showing the column that was unexpected
     */
    public function iShouldSeeErrorShowingUnexpectedColumns()
    {
        $this->iAmOnAdminOrgCsvUploadPage();

        $this->assertOnErrorMessage($this->expectedUnexpectedColumn);
    }
}
