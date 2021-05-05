<?php

namespace App\Tests\Behat\Common;

use Behat\Gherkin\Node\TableNode;

trait AuthenticationTrait
{
    /**
     * @Given I am logged in as :email with password :password
     */
    public function iAmLoggedInAsWithPassword($email, $password)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
    }

    /**
     * @Given I am logged in to admin as :email with password :password
     */
    public function iAmLoggedInToAdminAsWithPassword($email, $password)
    {
        $this->visitAdminPath('/logout');

        $this->iAmAtAdminLogin();
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->theFormShouldBeValid();
    }

    /**
     * @deprecated Use  I am on admin page "/login" instead
     * @Given I am on admin login page
     */
    public function iAmAtAdminLogin()
    {
        $this->visitAdminPath('/login');
    }

    /**
     * @When /^I open the (admin |)(activation|password reset) page for "(.+)"$/
     */
    public function openActivationOrPasswordResetPage($admin, $pageType, $email)
    {
        $url = sprintf('/admin/fixtures/user-registration-token?email=%s', $email);
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath($url);
        $token = $this->getSession()->getPage()->getContent();
        $this->visitAdminPath('/logout');

        $page = 'activation' === $pageType ? 'activate' : 'password-reset';

        if ('' === $admin || false === $admin) {
            $this->visitPath("/user/$page/$token");
        } else {
            $this->visitAdminPath("/user/$page/$token");
        }
    }

    /**
     * @Then the URL :url should be forbidden
     */
    public function theUrlShouldBeForbidden($url)
    {
        $previousUrl = $this->getSession()->getCurrentUrl();
        $this->visit($url);
        $this->assertResponseStatus(403);
        $this->visit($previousUrl);
    }

    /**
     * @Then the following :area pages should return the following status:
     */
    public function theFollowingPagesShouldReturnTheFollowingStatus($area, TableNode $table)
    {
        foreach ($table->getRowsHash() as $url => $expectedReturnCode) {
            'admin' == $area ? $this->visitAdminPath($url) : $this->visitPath($url);
            $actual = $this->getSession()->getStatusCode();
            if (intval($expectedReturnCode) !== intval($actual)) {
                throw new \RuntimeException("$url: Current response status code is $actual, but $expectedReturnCode expected.");
            }
        }
    }
}
