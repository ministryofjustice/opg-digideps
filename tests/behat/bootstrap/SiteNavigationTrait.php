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
     * @Given I am on admin page :path
     * @Given I go to admin page :path
     */
    public function iAmOnAdminPage($path)
    {
        $adminUrl = $this->getSymfonyParam('admin_host');
        $this->visitPath($adminUrl.$path);
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
     * @Given I am on the :report report overview page
     * @Given I go to the :report report overview page
     */
    public function iAmOnTheReport1Page($report)
    {
        $this->clickOnBehatLink('client-home');
        $this->clickOnBehatLink('report-' . $report);
    }
    
    /**
     * @Given I am on the accounts page of the :report report
     * @Given I go to the accounts page of the :report report
     */
    public function iAmOnTheReportAccountsPage($report)
    {
        $this->iAmOnTheReport1Page($report);
        $this->clickLink('edit-accounts');
    }
    
    /**
     * @Given I am on the account :accountNumber page of the :report report
     * @Given I go to the account :accountNumber page of the :report report
     */
    public function iAmOnTheReport1AccountPageByAccNumber($accountNumber, $report)
    {
        $this->iAmOnTheReportAccountsPage($report);
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