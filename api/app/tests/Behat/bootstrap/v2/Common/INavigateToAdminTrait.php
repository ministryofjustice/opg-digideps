<?php

namespace App\Tests\Behat\v2\Common;

trait INavigateToAdminTrait
{
    /**
     * @When I navigate to the admin clients search page
     */
    public function iNavigateToAdminClientsSearchPage()
    {
        $this->clickLink('Clients');
        $this->iAmOnAdminClientsSearchPage();
    }

    /**
     * @When I navigate to the admin add user page
     */
    public function iNavigateToAdminAddUserPage()
    {
        $this->pressButton('Add new user');
        $this->iAmOnAdminAddUserPage();
    }

    /**
     * @When I navigate to my admin user profile page
     */
    public function iNavigateToAdminUserProfilePage()
    {
        $this->clickLink('Your details');
        $mainElement = $this->getSession()->getPage()->find('xpath', '//main');
        $mainElement->clickLink('Your details');
    }

    /**
     * @When I navigate to the add a new user page
     */
    public function iNavigateToAddNewUser()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->clickLink('Add new user');
    }

    /**
     * @When I navigate to the admin analytics page
     */
    public function iNavigateToAdminAnalyticsSearchPage()
    {
        $this->clickLink('Analytics');
        $this->iAmOnAdminAnalyticsPage();
    }

    /**
     * @When I navigate to the upload users page
     */
    public function iNavigateToAdminUploadUsersPage()
    {
        $this->clickLink('Upload users');
    }

    /**
     * @When I navigate to the organisations page
     */
    public function iNavigateToOrganisationsPage()
    {
        $this->clickLink('Organisations');
        $this->iAmOnAdminOrganisationSearchPage();
    }

    /**
     * @When I navigate to the add organisation page
     */
    public function iNavigateToAddOrganisationPage()
    {
        $this->pressButton('Add a new organisation');
        $this->iAmOnAdminAddOrganisationPage();
    }

    /**
     * @When I navigate to the report checklist page
     */
    public function iNavigateToTheReportChecklistPage()
    {
        $this->clickLink('Checklist');
        $this->iAmOnAdminReportChecklistPage();
    }

    /**
     * @When I navigate to the admin report submissions page
     */
    public function iNavigateToAdminReportSubmissionsPage()
    {
        $this->clickLink('Submissions');
        $this->iAmOnAdminReportSubmissionsPage();
    }
}
