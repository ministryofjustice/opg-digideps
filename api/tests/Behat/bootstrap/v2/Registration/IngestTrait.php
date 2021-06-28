<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Tests\Behat\BehatException;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;

trait IngestTrait
{
    private array $clients = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $namedDeputies = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $organisations = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];
    private array $reports = ['expected' => 0, 'preUpdate' => 0, 'postUpdate' => 0];

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

        $this->assertOnAlertMessage(sprintf('%s clients', $this->clients['expected']));
        $this->assertOnAlertMessage(sprintf('%s named deputies', $this->namedDeputies['expected']));
        $this->assertOnAlertMessage(sprintf('%s organisation', $this->organisations['expected']));
        $this->assertOnAlertMessage(sprintf('%s reports', $this->reports['expected']));

        $this->assertIntEqualsInt($this->clients['expected'], $this->clients['postUpdate'] - $this->clients['preUpdate'], 'Post update minus pre update entities count');
        $this->assertIntEqualsInt($this->namedDeputies['expected'], $this->namedDeputies['postUpdate'] - $this->namedDeputies['preUpdate'], 'Post update minus pre update entities count');
        $this->assertIntEqualsInt($this->organisations['expected'], $this->organisations['postUpdate'] - $this->organisations['preUpdate'], 'Post update minus pre update entities count');
        $this->assertIntEqualsInt($this->reports['expected'], $this->reports['postUpdate'] - $this->reports['preUpdate'], 'Post update minus pre update entities count');
    }

    /**
     * @Then the count of the new entities added should be displayed
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
     * @When I visit the upload users page
     */
    public function iVisitTheUploadUsersPage()
    {
        throw new PendingException();
    }

    /**
     * @When I upload a :arg1 CSV that has a new made date and named deputy within the same org as the clients existing name deputy
     */
    public function iUploadACsvThatHasANewMadeDateAndNamedDeputyWithinTheSameOrgAsTheClientsExistingNameDeputy($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the clients made date and named deputy should be updated
     */
    public function theClientsMadeDateAndNamedDeputyShouldBeUpdated()
    {
        throw new PendingException();
    }

    /**
     * @When I upload a :arg1 CSV that has a new address and phone details for an existing named deputy
     */
    public function iUploadACsvThatHasANewAddressAndPhoneDetailsForAnExistingNamedDeputy($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the named deputies address and phone number should be updated
     */
    public function theNamedDeputiesAddressAndPhoneNumberShouldBeUpdated()
    {
        throw new PendingException();
    }
}
