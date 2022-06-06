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

    private function getHostedEnvironment(): string
    {
        $this->iVisitTheFrontendAvailabilityPage();
        $hostedEnvironmentListItem = $this->getSession()->getPage()->find('xpath', '//li[contains(.,"Hosted environment")]');
        $haystack = $hostedEnvironmentListItem->getHtml();

        return trim(substr(strrchr($haystack, ': '), 2));
    }

    /**
     * @Then I should see a banner confirming where the app is hosted
     */
    public function iShouldSeeABannerConfirmingTheAppIAmUsing()
    {
        $hostedEnvironment = $this->getHostedEnvironment();
        $this->iVisitAdminSearchUserPage();

        $banner = sprintf('You are now logged into the %s environment', $hostedEnvironment);
        $this->assertPageContainsText($banner);
    }

    /**
     * @Then I should not see a banner confirming where the app is hosted
     */
    public function iShouldNotSeeABannerConfirmingTheAppIAmUsing()
    {
        $hostedEnvironment = $this->getHostedEnvironment();

        if (in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            $this->iVisitAdminSearchUserPage();
        } else {
            $this->iVisitLayStartPage();
        }
        $banner = sprintf('You are now logged into the %s environment', $hostedEnvironment);
        $this->assertPageNotContainsText($banner);
        $this->assertElementNotOnPage('govuk-notification-banner__heading');
    }
}
