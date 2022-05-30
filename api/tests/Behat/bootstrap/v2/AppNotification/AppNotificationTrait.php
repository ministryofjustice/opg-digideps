<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\AppNotification;

trait AppNotificationTrait
{
    private string $validationMsg = 'Please enter a message';

    /**
     * @When I visit the service notification page
     */
    public function iVisitTheServiceNotificationPage()
    {
        $this->iVisitTheAdminNotificationPage();
        $this->iAmOnAdminNotificationPage();
    }

    /**
     * @When I set a service notification
     */
    public function iSetAServiceNotification()
    {
        $this->fillField('setting[content]', 'Lorem ipsum');
        $this->selectOption('setting[enabled]', '1');
        $this->pressButton('setting[save]');
    }

    /**
     * @When I set a service notification and see it on the login page
     */
    public function iSetAServiceNotificationAndSeeItOnTheClientPage()
    {
        $this->fillField('setting[content]', 'Lorem ipsum');
        $this->selectOption('setting[enabled]', '1');
        $this->pressButton('setting[save]');

        $this->visitAdminPath('/logout');
        $this->iVisitAdminLoginPage();

        $notification = $this->getSession()->getPage()->find('css', '.behat-region-service-notification > .opg-alert > .behat-region-alert-message > p')->getText();
        $this->assertStringEqualsString('Lorem ipsum', $notification, 'Service notification message');
    }

    /**
     * @When I set a service notification without a message
     */
    public function iSetAServiceNotificationWithoutAMessage()
    {
        $this->fillField('setting[content]', '');
        $this->selectOption('setting[enabled]', '1');
        $this->pressButton('setting[save]');
    }

    /**
     * @Then I should see the service message on the client login page
     */
    public function iShouldSeeTheServiceMessageOnTheClientLoginPage()
    {
        $this->iVisitTheClientLoginPage();
        $this->iAmOnClientLoginPage();

        $notification = $this->getSession()->getPage()->find('css', '.behat-region-service-notification > .opg-alert > .behat-region-alert-message > p')->getText();
        $this->assertStringEqualsString('Lorem ipsum', $notification, 'Service notification message');
    }

    /**
     * @Then I turn off the service notification and can no longer see it on the client login page
     */
    public function iTurnOffTheServiceNotificationAndCanNoLongerSeeItOnTheClientLoginPage()
    {
        $this->loginToAdminAs($this->loggedInUserDetails->getUserEmail());

        $this->iVisitTheAdminNotificationPage();
        $this->iAmOnAdminNotificationPage();

        $this->selectOption('setting[enabled]', '0');
        $this->pressButton('setting[save]');

        $this->iVisitTheClientLoginPage();
        $this->iAmOnClientLoginPage();

        $el = $this->getSession()->getPage()->find('css', '.behat-region-service-notification');
        $this->assertIsNull($el, 'Service notification message does not exist');
    }

    /**
     * @Then I should see a validation error
     */
    public function iShouldSeeAValidationError()
    {
        $this->assertOnErrorMessage($this->validationMsg);
    }

    /**
     * @Then I should see a banner confirming the :hostedEnvironment version of the app I am using
     */
    public function iShouldSeeABannerConfirmingTheVersionOfTheAppIAmUsing(string $hostedEnvironment)
    {
        $this->assertPageContainsText(sprintf('You are now logged into the %s environment', $hostedEnvironment));
    }

    /**
     * @Then I should not see the banner confirming the version of the app I am using
     */
    public function iShouldNotSeeTheBannerConfirmingTheVersionOfTheAppIAmUsing()
    {
        $this->assertElementNotOnPage('govuk-notification-banner__heading');
    }
}
