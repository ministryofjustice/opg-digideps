<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\AppNotification;

trait AppNotificationTrait
{
    private string $validationMsg = 'Please enter a message';

    /**
     * @When I visit the service notification page
     */
    public function iNavigateToTheClientsSearchPage()
    {
        $this->iVisitTheNotificationPage();
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
     * @When I set a service notification and see it on the client page
     */
    public function iSetAServiceNotificationAndSeeItOnTheClientPage()
    {
        $this->fillField('setting[content]', 'Lorem ipsum');
        $this->selectOption('setting[enabled]', '1');
        $this->pressButton('setting[save]');

        $this->iVisitTheClientLoginPage();
        $this->iAmOnClientLoginPage();

        $notification = $this->getSession()->getPage()->find('css', '.behat-region-service-notification > .behat-region-alert-message > p')->getText();
        assert('Lorem ipsum' == $notification);
    }

    /**
     * @When I set a service notification without a message
     */
    public function iSetAServiceNotificationWithoutAMessage()
    {
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

        $notification = $this->getSession()->getPage()->find('css', '.behat-region-service-notification > .behat-region-alert-message > p')->getText();
        var_dump($notification);
        assert('Lorem ipsum' == $notification);
    }

    /**
     * @Then I turn off the service notification and can no longer see it on the client login page
     */
    public function iTurnOffTheServiceNotificationAndCanNoLongerSeeItOnTheClientLoginPage()
    {
        $this->iVisitTheNotificationPage();
        $this->iAmOnAdminNotificationPage();

        $this->selectOption('setting[enabled]', '1');
        $this->pressButton('setting[save]');

        $this->iVisitTheClientLoginPage();
        $this->iAmOnClientLoginPage();

        $el = $this->getSession()->getPage()->find('css', '.behat-region-alert-message');
        assert(null == $el);
    }

    /**
     * @Then I should see a validation error
     */
    public function iShouldSeeAValidationError()
    {
        $this->assertOnErrorMessage($this->validationMsg);
    }
}
