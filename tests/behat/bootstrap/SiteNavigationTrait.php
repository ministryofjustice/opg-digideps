<?php

namespace DigidepsBehat;

trait SiteNavigationTrait
{
    
    /**
     * @Given I am on client home :clientHome and I click first report :link
     */
    public function iAmOnClientHomeAndClickReport($clientHome,$link)
    {
        $this->clickOnBehatLink($clientHome);
        $this->clickOnBehatLink($link);
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am on client home page :client_home
     */
    public function iAmOnClientHome($client_home)
    {
        $this->clickOnBehatLink($client_home);
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am on the feedback page
     * @Given I goto the feedback page
     * @And I select the feedback link
     */
    public function feedbackPage()
    {
        $this->visit('/feedback');
    }
    
    /**
     * @Given I am on the first report overview page
     * @Given I go to the first report overview page
     */
    public function iAmOnTheReport1Page()
    {
        $this->clickOnBehatLink('client-home');
        $this->clickOnBehatLink('report-n1');
    }
    
    /**
     * @Given I am on the accounts page of the first report
     * @Given I go to the accounts page of the first report
     */
    public function iAmOnTheReport1AccountsPage()
    {
        $this->iAmOnTheReport1Page();
        $this->clickLink('tab-accounts');
    }
    
    /**
     * @Given I am on the account :accountNumber page of the first report
     * @Given I go to the account :accountNumber page of the first report
     */
    public function iAmOnTheReport1AccountPageByAccNumber($accountNumber)
    {
        $this->iAmOnTheReport1AccountsPage();
        $this->clickOnBehatLink('account-' . $accountNumber);
    }
    
    /**
     * @Given I goto the terms page
     */
    public function iGotoTheTermsPage()
    {
        $this->visit('/terms');
    }
    
}