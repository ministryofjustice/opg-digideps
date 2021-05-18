<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait HealthAndLifestyleTrait
{
    private array $lifestyleAnswers = [];

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
        $this->lifestyleAnswers[0][] = 'details of any care appointments';
        $this->lifestyleAnswers[0][] = 'Please answer this question';
        $this->clickLink('Skip this question for now');
        $this->iAmOnLifestyleActivitiesPage();
        $this->lifestyleAnswers[1][] = 'leisure or social activities';
        $this->lifestyleAnswers[1][] = 'Please answer this question';
        $this->clickLink('Skip this question for now');
    }

    /**
     * @When I fill in details about clients health and care appointments
     */
    public function iFillInDetailsHealthCareAppointments()
    {
        $appointmentText = $this->faker->text(200);
        $this->iAmOnLifestyleDetailsPage();
        $this->lifestyleAnswers[0][] = 'details of any care appointments';
        $this->lifestyleAnswers[0][] = $appointmentText;
        $this->fillField('lifestyle[careAppointments]', $appointmentText);
        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm that client takes part in no leisure or social activities
     */
    public function iConfirmClientTakesPartNoLeisureOrSocialActivities()
    {
        $leisureText = $this->faker->text(200);
        $this->iAmOnLifestyleActivitiesPage();
        $this->lifestyleAnswers[1][] = 'leisure or social activities';
        $this->lifestyleAnswers[1][] = 'no';
        $this->selectOption('lifestyle[doesClientUndertakeSocialActivities]', 'no');
        $this->lifestyleAnswers[2][] = 'does not take part in any leisure or social activities';
        $this->lifestyleAnswers[2][] = $leisureText;
        $this->fillField('lifestyle[activityDetailsNo]', $leisureText);
        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm that client takes part in leisure and social activities
     */
    public function iConfirmClientTakesPartLeisureAndSocialActivities()
    {
        $leisureText = $this->faker->text(200);
        $this->iAmOnLifestyleActivitiesPage();
        $this->lifestyleAnswers[1][] = 'leisure or social activities';
        $this->lifestyleAnswers[1][] = 'yes';
        $this->selectOption('lifestyle[doesClientUndertakeSocialActivities]', 'yes');
        $this->lifestyleAnswers[2][] = 'takes part in and how often';
        $this->lifestyleAnswers[2][] = $leisureText;
        $this->fillField('lifestyle[activityDetailsYes]', $leisureText);
        $this->pressButton('Save and continue');
    }

    /**
     * @When I edit the existing lifestyle section answers
     */
    public function iEditTheExistingLifestyleSectionAnswers()
    {
        $this->iAmOnLifestyleSummaryPage();
        $urlRegex = '/report\/.*\/lifestyle\/step\/1.*/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->iFillInDetailsHealthCareAppointments();
        $this->iAmOnLifestyleSummaryPage();
        $urlRegex = '/report\/.*\/lifestyle\/step\/2.*/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->iConfirmClientTakesPartLeisureAndSocialActivities();
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
        $this->selectOption('lifestyle[doesClientUndertakeSocialActivities]', 'no');
        $this->pressButton('Save and continue');
        $expectedMessage = 'Give us more information about why the client does not take part in any activity';
        $this->assertOnAlertMessage($expectedMessage);
        $this->selectOption('lifestyle[doesClientUndertakeSocialActivities]', 'yes');
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
        $this->expectedResultsDisplayed(
            0,
            $this->lifestyleAnswers,
            'Health and Lifestyle details'
        );
    }
}
