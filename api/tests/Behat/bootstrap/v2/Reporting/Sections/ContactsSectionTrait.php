<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait ContactsSectionTrait
{
    private string $reasonForNoContacts;

    private string $contactName;
    private string $contactRelationship;
    private string $contactExplanation;
    private string $contactAddress;
    private string $contactAddress2;
    private string $contactCounty;
    private string $contactPostcode;
    private string $contactCountry;

    private array $formValuesEntered = [];

    private function setContactFormValues(bool $noContacts)
    {
        if ($noContacts) {
            $this->formValuesEntered[] = $this->reasonForNoContacts = $this->faker->text(200);

            return;
        }

        $this->formValuesEntered[] = $this->contactName = $this->faker->name;
        $this->formValuesEntered[] = $this->contactRelationship = $this->faker->text(50);
        $this->formValuesEntered[] = $this->contactExplanation = $this->faker->text(200);
        $this->formValuesEntered[] = $this->contactAddress = $this->faker->streetName;
        $this->formValuesEntered[] = $this->contactAddress2 = $this->faker->city;
        $this->formValuesEntered[] = $this->contactCounty = $this->faker->county;
        $this->formValuesEntered[] = $this->contactPostcode = $this->faker->postcode;
        $this->formValuesEntered[] = $this->contactCountry = 'United Kingdom';
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
            $this->throwContextualException(sprintf('Not on contacts start page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @Given I view and start the contacts report section
     */
    public function iViewAndStartContactsSection()
    {
        $this->iViewContactsSection();

        $this->clickLink('Start contacts');
    }

    /**
     * @Given there are no contacts to add
     */
    public function thereAreNoContactsToAdd()
    {
        $this->setContactFormValues(true);

        $this->selectOption('contact_exist[hasContacts]', 'no');
        $this->fillField('contact_exist_reasonForNoContacts', $this->reasonForNoContacts);

        $this->pressButton('Save and continue');
    }

    /**
     * @Given there are contacts to add
     */
    public function thereAreContactsToAdd()
    {
        $this->selectOption('contact_exist[hasContacts]', 'yes');
        $this->pressButton('Save and continue');

        $this->iAmOnAddAContactPage();
    }

    /**
     * @When I enter valid contact details
     */
    public function iEnterValidContactDetails()
    {
        $this->setContactFormValues(false);

        $this->fillField('contact_contactName', $this->contactName);
        $this->fillField('contact_relationship', $this->contactRelationship);
        $this->fillField('contact_explanation', $this->contactExplanation);
        $this->fillField('contact_address', $this->contactAddress);
        $this->fillField('contact_address2', $this->contactAddress2);
        $this->fillField('contact_county', $this->contactCounty);
        $this->fillField('contact_postcode', $this->contactPostcode);
        $this->selectOption('contact_country', $this->contactCountry);

        $this->pressButton('Save and continue');

        $this->iAmOnContactsAddAnotherPage();
    }

    /**
     * @When I enter another contacts details
     */
    public function iEnterAnotherContactsDetails()
    {
        $this->selectOption('add_another[addAnother]', 'yes');
        $this->pressButton('Continue');

        $this->iAmOnAddAContactPage();

        $this->iEnterValidContactDetails();

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
        // We use a table for displaying contact details and a dl for no contacts
        $table = $this->getSession()->getPage()->find('css', 'table');
        $descriptionList = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table && !$descriptionList) {
            $this->throwContextualException('A table or dl element was not found on the page');
        }

        $missingText = [];
        $html = $table ? $table->getHtml() : $descriptionList->getHtml();

        foreach ($this->formValuesEntered as $contactDetail) {
            $textVisible = str_contains($html, $contactDetail);

            if (!$textVisible) {
                $missingText[] = $contactDetail;
            }
        }

        if (!empty($missingText)) {
            $tableType = $table ? 'table' : 'dl';

            $this->throwContextualException(
                sprintf(
                    'A %s was found but the row with the expected text was not found. Missing text: %s. HTML found: %s',
                    $tableType,
                    implode(', ', $missingText),
                    $html
                )
            );
        }
    }
}
