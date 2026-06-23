<?php

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

trait INavigateToAdminTrait
{
    /**
     * @When I navigate to the admin clients search page
     */
    public function iNavigateToAdminClientsSearchPage(): void
    {
        $this->clickLink('Clients');
        $this->iAmOnAdminClientsSearchPage();
    }

    /**
     * @When I navigate to the admin add user page
     */
    public function iNavigateToAdminAddUserPage(): void
    {
        $this->pressButton('Add new user');
        $this->iAmOnAdminAddUserPage();
    }

    /**
     * @When I navigate to my admin user profile page
     */
    public function iNavigateToAdminUserProfilePage(): void
    {
        $this->clickLink('Your details');
        $mainElement = $this->getSession()->getPage()->find('xpath', '//main');
        $mainElement->clickLink('Your details');
    }

    public function iNavigateToAddNewUser(): void
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->clickLink('Add new user');
    }

    /**
     * @When I navigate to the admin analytics page
     */
    public function iNavigateToAdminAnalyticsSearchPage(): void
    {
        $this->clickLink('Analytics');
        $this->iAmOnAdminAnalyticsPage();
    }

    /**
     * @When I navigate to the organisations page
     */
    public function iNavigateToOrganisationsPage(): void
    {
        $this->clickLink('Organisations');
        $this->iAmOnAdminOrganisationSearchPage();
    }

    /**
     * @When I navigate to the add organisation page
     */
    public function iNavigateToAddOrganisationPage(): void
    {
        $this->pressButton('Add a new organisation');
        $this->iAmOnAdminAddOrganisationPage();
    }

    /**
     * @When I navigate to the admin report submissions page
     */
    public function iNavigateToAdminReportSubmissionsPage(): void
    {
        $this->clickLink('Submissions');
        $this->iAmOnAdminReportSubmissionsPage();
    }
}
