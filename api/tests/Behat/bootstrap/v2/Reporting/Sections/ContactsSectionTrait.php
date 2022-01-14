<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait ContactsSectionTrait
{
    private bool $hasContacts = false;

    /**
     * @Given I view and start the contacts report section
     */
    public function iViewAndStartContactsSection()
    {
        $this->iViewContactsSection();

        $this->clickLink('Start contacts');
    }

    /**
     * @Given I view the contacts report section
     */
    public function iViewContactsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'contacts');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/contacts$/', $currentUrl);

        if (!$onSummaryPage) {
            throw new BehatException(sprintf('Not on contacts start page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @Given there are no contacts to add
     */
    public function thereAreNoContactsToAdd()
    {
        $this->chooseOption('contact_exist[hasContacts]', 'no', 'hasContacts');
        $this->fillInField('contact_exist_reasonForNoContacts', $this->faker->text(30), 'hasContacts');

        $this->pressButton('Save and continue');
    }

    /**
     * @Given there are contacts to add
     */
    public function thereAreContactsToAdd()
    {
        $this->chooseOption('contact_exist[hasContacts]', 'yes', 'hasContacts');
        $this->pressButton('Save and continue');
        $this->hasContacts = true;

        $this->iAmOnAddAContactPage();
    }

    /**
     * @When I enter another contacts details
     */
    public function iEnterAnotherContactsDetails()
    {
        $this->chooseOption('add_another[addAnother]', 'yes');
        $this->pressButton('Continue');

        $this->iAmOnAddAContactPage();

        $this->iEnterValidContactDetails();

        $this->iAmOnContactsAddAnotherPage();
    }

    /**
     * @When I enter valid contact details
     */
    public function iEnterValidContactDetails()
    {
        $this->fillInField('contact_contactName', $this->faker->name(), 'contactDetails');
        $this->fillInField('contact_relationship', $this->faker->text(50), 'contactDetails');
        $this->fillInField('contact_explanation', $this->faker->text(200), 'contactDetails');
        $this->fillInField('contact_address', $this->faker->streetName(), 'contactDetails');
        $this->fillInField('contact_address2', $this->faker->city(), 'contactDetails');
        $this->fillInField('contact_county', $this->faker->county, 'contactDetails');
        $this->fillInField('contact_postcode', $this->faker->postcode(), 'contactDetails');
        $this->chooseOption('contact_country', 'United Kingdom', 'contactDetails');

        $this->pressButton('Save and continue');

        $this->iAmOnContactsAddAnotherPage();
    }

    /**
     * @When there are no further contacts to add
     */
    public function thereAreNoFurtherContactsToAdd()
    {
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');

        $this->iAmOnContactsSummaryPage();
    }

    /**
     * @Then the contacts summary page should contain the details I entered
     */
    public function contactSummaryPageContainsExpectedText()
    {
        $this->expectedResultsDisplayedSimplified('hasContacts');

        if ($this->hasContacts) {
            $this->expectedResultsDisplayedSimplified('contactDetails');
        }
    }
}
