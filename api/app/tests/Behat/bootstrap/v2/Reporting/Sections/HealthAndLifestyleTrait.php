<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait HealthAndLifestyleTrait
{
    /**
     * @When I view and start the health and lifestyle report section
     */
    public function iViewAndStartHealthLifestyleSection()
    {
        $this->iVisitHealthAndLifestyleSection();
        $this->clickLink('Start health and lifestyle');
    }

    /**
     * @When I skip both lifestyle sections
     */
    public function iSkipBothLifeStyleSections()
    {
        $this->iAmOnLifestyleDetailsPage();
        $this->clickLink('Skip this question for now');

        $this->iAmOnLifestyleActivitiesPage();
        $this->clickLink('Skip this question for now');
    }

    /**
     * @When I fill in details about clients health and care appointments
     */
    public function iFillInDetailsHealthCareAppointments()
    {
        $this->iAmOnLifestyleDetailsPage();

        $this->fillInField('lifestyle[careAppointments]', $this->faker->text(200), 'health-lifestyle-care');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm that client takes part in no leisure or social activities
     */
    public function iConfirmClientTakesPartNoLeisureOrSocialActivities()
    {
        $this->takesPartLeisureSocial('no');
    }

    /**
     * @When I confirm that client takes part in leisure and social activities
     */
    public function iConfirmClientTakesPartLeisureAndSocialActivities()
    {
        $this->takesPartLeisureSocial('yes');
    }

    /**
     * @When I edit the lifestyle section answers as client takes part in activities
     */
    public function iEditLifestyleSectionAnswersYes()
    {
        $this->removeAnswerFromSection(
            'lifestyle[doesClientUndertakeSocialActivities]',
            'health-lifestyle-leisure',
        );

        $locator = sprintf(
            "//dd[a[contains(@href,'/report/%s/lifestyle/step/2')]]/..",
            $this->loggedInUserDetails->getCurrentReportId()
        );

        $careAppointmentsRow = $this->getSession()->getPage()->find('xpath', $locator);
        $careAppointmentsRow->clickLink('Edit');

        $this->takesPartLeisureSocial('no');
    }

    /**
     * @When I edit the lifestyle section answers as client doesn't take part in activities
     */
    public function iEditLifestyleSectionAnswersNo()
    {
        $this->removeAnswerFromSection(
            'lifestyle[doesClientUndertakeSocialActivities]',
            'health-lifestyle-leisure'
        );

        $locator = sprintf(
            "//dd[a[contains(@href,'/report/%s/lifestyle/step/2')]]/..",
            $this->loggedInUserDetails->getCurrentReportId()
        );

        $leisureActivityRow = $this->getSession()->getPage()->find('xpath', $locator);
        $leisureActivityRow->clickLink('Edit');

        $this->takesPartLeisureSocial('no');
    }

    /**
     * @When I do not enter any appointment details
     */
    public function iDoNotEnterAnyAppointmentDetails()
    {
        $this->iAmOnLifestyleDetailsPage();
        $this->pressButton('Save and continue');
    }

    /**
     * @When I do not enter any leisure and activity details
     */
    public function iDoNotEnterAnyLeisureActivityDetails()
    {
        $this->iAmOnLifestyleActivitiesPage();
        $this->pressButton('Save and continue');
    }

    /**
     * @When I receive the expected lifestyle activities validation message
     */
    public function iReceiveExpectedLifestyleActivitiesValidationMessage()
    {
        $expectedMessage = "Please select either 'Yes' or 'No'";
        $this->assertOnAlertMessage($expectedMessage);

        $this->chooseOption('lifestyle[doesClientUndertakeSocialActivities]', 'no');
        $this->pressButton('Save and continue');
        $expectedMessage = 'Give us more information about why the client does not take part in any activity';
        $this->assertOnAlertMessage($expectedMessage);

        $this->chooseOption('lifestyle[doesClientUndertakeSocialActivities]', 'yes');
        $this->pressButton('Save and continue');
        $expectedMessage = 'Give us more details about the different types of activities client takes part in and how often';
        $this->assertOnAlertMessage($expectedMessage);
    }

    /**
     * @When I receive the expected lifestyle appointments validation message
     */
    public function iReceiveExpectedLifestyleAppointmentsValidationMessage()
    {
        $expectedMessage = "Please describe client's health and provide details of any care appointments attended";
        $this->assertOnAlertMessage($expectedMessage);
        $this->iFillInDetailsHealthCareAppointments();
    }

    /**
     * @Then I should see the expected lifestyle section summary
     */
    public function iShouldSeeTheExpectedLifestyleSummary()
    {
        $this->iAmOnLifestyleSummaryPage();

        $this->expectedResultsDisplayedSimplified();
    }

    private function takesPartLeisureSocial($takesPart)
    {
        $this->iAmOnLifestyleActivitiesPage();

        $this->chooseOption(
            'lifestyle[doesClientUndertakeSocialActivities]',
            $takesPart,
            'health-lifestyle-leisure'
        );

        $this->fillInField(
            'yes' == $takesPart ? 'lifestyle[activityDetailsYes]' : 'lifestyle[activityDetailsNo]',
            $this->faker->text(200),
            'health-lifestyle-leisure'
        );

        $this->pressButton('Save and continue');
    }
}
