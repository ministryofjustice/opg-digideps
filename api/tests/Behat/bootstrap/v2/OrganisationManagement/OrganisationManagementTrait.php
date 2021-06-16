<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\OrganisationManagement;

trait OrganisationManagementTrait
{
    private string $organisationName;
    private string $emailDomain;

    /**
     * @Then I add an active organisation
     */
    public function iAddAnActiveOrganisation()
    {
        $this->organisationName = $this->faker->company;

        $email = $this->faker->companyEmail;
        $this->emailDomain = substr($email, strpos($email, '@') + 1);

        $this->fillField('organisation[name]', $this->organisationName);
        $this->selectOption('organisation[emailIdentifierType]', 'domain');
        $this->fillField('organisation[emailDomain]', $this->emailDomain);
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

        $foundOrgName = false;
        $foundOrgEmail = false;
        $foundActive = false;
        foreach ($tableDataElements as $td) {
            if (strtolower($td->getText()) == strtolower($this->organisationName)) {
                $foundOrgName = true;
            } elseif (strtolower($td->getText()) == '*@'.strtolower($this->emailDomain)) {
                $foundOrgEmail = true;
            } elseif ('active' == strtolower($td->getText())) {
                $foundActive = true;
            }
        }

        if (!$foundOrgName) {
            $this->throwContextualException(sprintf('Could not find Organisation Name. Expected name is %s', $this->organisationName));
        } elseif (!$foundOrgEmail) {
            $this->throwContextualException(sprintf('Could not find Organisation Email Domain. Expected Email Domain name is %s', $this->emailDomain));
        } elseif (!$foundActive) {
            $this->throwContextualException('Could not find Active flag');
        }
    }

    /**
     * @When I view the organisation
     */
    public function iViewOrganisation()
    {
        $this->iAmOnAdminOrganisationSearchPage();

        $this->clickLink($this->organisationName);

        $this->iAmOnAdminOrganisationOverviewPage();
    }

    /**
     * @Then I should see the organisation is empty
     */
    public function iSeeEmptyOrganisation()
    {
        $this->iAmOnAdminOrganisationOverviewPage();

        $text = 'This organisation does not have any members';
        $this->assertPageContainsText($text);

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
        $this->assertStringEqualsString($this->emailDomain, $tableValues[1], 'Asserting Email Domain found on Overview page');
        $this->assertStringEqualsString('0', $tableValues[3], 'Asserting Zero Users found Overview page');
        $this->assertStringEqualsString('0', $tableValues[5], 'Asserting Zero Clients found on Overview page');
    }
}
