<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\OrganisationManagement;

use App\Tests\Behat\BehatException;

trait OrganisationManagementTrait
{
    private array $organisations;

    /**
     * @When I add an organisation
     */
    public function iAddAnActiveOrganisation()
    {
        $orgName = 'My Organisation '.$this->testRunId;

        $domain = $this->testRunId.'.com';

        $this->organisations[] = ['Name' => $orgName, 'Email' => $domain, 'Active' => true];

        $this->fillField('organisation[name]', $orgName);
        $this->selectOption('organisation[emailIdentifierType]', 'domain');
        $this->fillField('organisation[emailDomain]', $domain);
        $this->selectOption('organisation[isActivated]', '1');
        $this->pressButton('Save organisation');
    }

    /**
     * @Then I should see the organisation
     */
    public function iSeeOrganisationInSearch()
    {
        $this->iAmOnAdminOrganisationSearchPage();

        $xpath = '//td';
        $tableDataElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $formattedDataElements = [];

        foreach ($tableDataElements as $td) {
            $formattedDataElements[] = strtolower($td->getText());
        }

        foreach ($this->organisations as $org) {
            if (!in_array(strtolower($org['Name']), $formattedDataElements)) {
                throw new BehatException(sprintf('Could not find Organisation Name. Expected name is %s', $org['Name']));
            }
            if (!in_array(strtolower('*@'.$org['Email']), $formattedDataElements)) {
                throw new BehatException(sprintf('Could not find Organisation Email Domain. Expected Email Domain name is %s', '*@'.$org['Email']));
            }
        }
    }

    /**
     * @When I view the organisation
     */
    public function iViewOrganisation()
    {
        $this->iAmOnAdminOrganisationSearchPage();

        $org = array_pop($this->organisations);

        $this->clickLink($org['Name']);

        $matches = [];
        preg_match('/[^\/]+$/', $this->getCurrentUrl(), $matches);
        $orgId = $matches[0];

        $org['Id'] = $orgId;
        $this->organisations[] = $org;

        $this->iAmOnAdminOrganisationOverviewPage();
    }

    /**
     * @Then I should see the organisation is empty
     */
    public function iSeeEmptyOrganisation()
    {
        $this->assertOverviewPage(0, 0);
    }

    /**
     * @When I add :number professional users to the organisation
     */
    public function iAddProfUsersToOrganisation($numberOfUsers)
    {
        $users = $this->createAdditionalProfHealthWelfareUsers(intval($numberOfUsers));

        foreach ($users as $user) {
            $this->iAmOnAdminOrganisationOverviewPage();
            $this->pressButton('Add user');

            $this->iAmOnAddUserToOrganisationPage();
            $this->fillField('organisation_add_user[email]', $user->getUserEmail());

            $this->pressButton('Find user');
            $this->pressButton('Add user to organisation');
        }
    }

    /**
     * @When I add a lay user to the organisation
     */
    public function iAddLayUserToOrganisation()
    {
        $this->iAmOnAdminOrganisationOverviewPage();
        $this->pressButton('Add user');

        $this->iAmOnAddUserToOrganisationPage();
        $this->fillField('organisation_add_user[email]', $this->layDeputyNotStartedPfaHighAssetsDetails->getUserEmail());

        $this->pressButton('Find user');
    }

    /**
     *@Then I should see an unsuitable role error
     */
    public function iShouldSeeUnsuitableRoleError()
    {
        $this->assertPageContainsText('User has unsuitable role to be in this organisation');
        $this->clickLink('Back');
    }

    /**
     *@Then I should see the organisation has :number users
     */
    public function iShouldSeeUsersInOrganisation($numberOfUsers)
    {
        $this->iAmOnAdminOrganisationOverviewPage();
        $this->assertOverviewPage(intval($numberOfUsers), 0);
    }

    private function assertOverviewPage(int $users, int $clients)
    {
        $this->iAmOnAdminOrganisationOverviewPage();

        if (0 == $users) {
            $text = 'This organisation does not have any members';
            $this->assertPageContainsText($text);
        }

        $xpath = "//div[contains(@class, 'govuk-summary-list__row')]";
        $listSummaryRowItems = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $tableValues = [];
        if (count($listSummaryRowItems) > 0) {
            foreach ($listSummaryRowItems as $listSummaryRowItemKey => $listSummaryRowItem) {
                $xpath = '//dt|//dd';
                $descriptionDataItems = $listSummaryRowItem->findAll('xpath', $xpath);

                foreach ($descriptionDataItems as $item) {
                    $tableValues[] = trim(strval($item->getText()));
                }
            }
        }

        $this->assertStringEqualsString(strval($users), $tableValues[3], 'Asserting Users found on Overview page');
        $this->assertStringEqualsString(strval($clients), $tableValues[5], 'Asserting Clients found on Overview page');
    }

    /**
     * @Then I should not see the organisation
     */
    public function iDoNotSeeOrganisationInSearch()
    {
        $this->iAmOnAdminOrganisationSearchPage();

        $xpath = '//td';
        $tableDataElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $formattedDataElements = [];

        foreach ($tableDataElements as $td) {
            $formattedDataElements[] = strtolower($td->getText());
        }

        $orgName = end($this->organisations)['Name'];

        if (in_array(strtolower($orgName), $formattedDataElements)) {
            throw new BehatException(sprintf('Found Organisation Name: %s. Expected organisation to be deleted.', $orgName));
        }

        $email = end($this->organisations)['Email'];

        if (in_array(strtolower('*@'.$email), $formattedDataElements)) {
            throw new BehatException(sprintf('Found Organisation Email Domain: %s. Expected organisation to be deleted.', $email));
        }
    }

    /**
     * @When I delete the organisation
     */
    public function iDeleteTheOrganisation()
    {
        $this->iAmOnAdminOrganisationSearchPage();

        $orgId = end($this->organisations)['Id'];
        $deleteLink = sprintf('/admin/organisations/%s/delete', $orgId);

        $links = $this->getSession()->getPage()->findAll('css', 'a');
        $foundLink = false;

        foreach ($links as $link) {
            if (!$foundLink) {
                if (str_ends_with($link->getAttribute('href'), $deleteLink)) {
                    $foundLink = true;
                    $link->click();
                    $this->pressButton('Yes, remove organisation');
                }
            }
        }

        if (!$foundLink) {
            throw new BehatException(sprintf('Could not find link to delete organisation. Organisation Id: %s', $orgId));
        }
    }

    /**
     * @Then I should not be able to delete the organisation
     */
    public function iShouldNotBeAbleToDeleteTheOrganisation()
    {
        $this->iAmOnAdminOrganisationSearchPage();

        $links = $this->getSession()->getPage()->findAll('css', 'a');

        foreach ($links as $link) {
            if (str_ends_with($link->getAttribute('href'), '/delete')) {
                throw new BehatException(sprintf('Found unexpected delete link on Organisation Search Page'));
            }
        }
    }

    /**
     * @When I edit the organisation name
     */
    public function iEditOrganisationName()
    {
        $this->iAmOnAdminOrganisationOverviewPage();

        $this->pressButton('Edit organisation');

        $organisation = array_pop($this->organisations);

        $organisation['Name'] = $organisation['Name'].' Edit';

        $this->organisations[] = $organisation;

        $this->fillField('organisation[name]', $organisation['Name']);

        $this->pressButton('Save organisation');
    }
}
