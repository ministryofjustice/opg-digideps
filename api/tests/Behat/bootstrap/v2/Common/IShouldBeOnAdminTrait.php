<?php

namespace App\Tests\Behat\v2\Common;

trait IShouldBeOnAdminTrait
{
    /**
     * @Then I should be on the admin clients search page
     */
    public function iAmOnAdminClientsSearchPage()
    {
        return $this->iAmOnPage('/admin\/client\/search$/');
    }

    /**
     * @Then I should be on the admin client details page
     */
    public function iAmOnAdminClientDetailsPage()
    {
        return $this->iAmOnPage('/admin\/client\/.*\/details$/');
    }

    /**
     * @Then I should be on the admin client discharge page
     */
    public function iAmOnAdminClientDischargePage()
    {
        return $this->iAmOnPage('/admin\/client\/.*\/discharge/');
    }

    /**
     * @Then I should be on the admin users search page
     */
    public function iAmOnAdminUsersSearchPage()
    {
        return $this->iAmOnPage('/admin\//');
    }

    /**
     * @Then I should be on the admin upload users page
     */
    public function iAmOnAdminUploadUsersPage()
    {
        return $this->iAmOnPage('/admin\/upload/');
    }

    /**
     * @Then I should be on the admin org csv upload page
     */
    public function iAmOnAdminOrgCsvUploadPage()
    {
        return $this->iAmOnPage('/admin\/org-csv-upload/');
    }

    /**
     * @Then I should be on the admin lay csv upload page
     */
    public function iAmOnAdminLayCsvUploadPage()
    {
        return $this->iAmOnPage('/admin\/casrec-upload/');
    }

    /**
     * @Then I should be on the admin view user page
     */
    public function iAmOnAdminViewUserPage()
    {
        return $this->iAmOnPage('/admin\/user\/[0-9].*/');
    }

    /**
     * @Then I should be on the admin add user page
     */
    public function iAmOnAdminAddUserPage()
    {
        return $this->iAmOnPage('/admin\/user-add$/');
    }

    /**
     * @Then I should be on the admin edit user page
     */
    public function iAmOnAdminEditUserPage()
    {
        return $this->iAmOnPage('/admin\/edit-user.*$/');
    }

    /**
     * @Then I should be on the admin delete confirm user page
     */
    public function iAmOnAdminDeleteConfirmUserPage()
    {
        return $this->iAmOnPage('/admin\/delete-confirm\/[0-9].*$/');
    }

    /**
     * @Then I should be on the admin organisation search page
     */
    public function iAmOnAdminOrganisationSearchPage()
    {
        return $this->iAmOnPage('/admin\/organisations\//');
    }

    /**
     * @Then I should be on the admin add organisation page
     */
    public function iAmOnAdminAddOrganisationPage()
    {
        return $this->iAmOnPage('/admin\/organisations\/add$/');
    }

    /**
     * @Then I should be on the admin organisation overview page
     */
    public function iAmOnAdminOrganisationOverviewPage()
    {
        return $this->iAmOnPage('/admin\/organisations\/.*$/');
    }

    /**
     * @Then I should be on the add user to organisation page
     */
    public function iAmOnAddUserToOrganisationPage()
    {
        return $this->iAmOnPage('/admin\/organisations\/.*\/add-user$/');
    }

    /**
     * @Then I should be on the admin stats page
     */
    public function iAmOnAdminStatsPage()
    {
        return $this->iAmOnPage('/admin\/stats$/');
    }

    /**
     * @Then I should be on the admin stats user research page
     */
    public function iAmOnAdminStatsUserResearchPage()
    {
        return $this->iAmOnPage('/admin\/stats\/user-research$/');
    }

    /**
     * @Then I should be on the admin stats satisfaction page
     */
    public function iAmOnAdminStatsSatisfactionPage()
    {
        return $this->iAmOnPage('/admin\/stats\/satisfaction$/');
    }

    public function iAmOnAdminManageReportPage()
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage$/');
    }

    public function iAmOnAdminManageReportConfirmPage()
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage-confirm$/');
    }

    public function iAmOnAdminManageCloseReportConfirmPage()
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage-close-report-confirm$/');
    }

    /**
     * @Then I should be on the admin report checklist page
     */
    public function iAmOnAdminReportChecklistPage()
    {
        return $this->iAmOnPage('/admin\/report\/.*\/checklist$/');
    }

    /**
     * @Then I should be on the admin report checklist submitted page
     */
    public function iAmOnAdminReportChecklistSubmittedPage()
    {
        return $this->iAmOnPage('/admin\/report\/.*\/checklist-submitted$/');
    }

    public function iAmOnAdminReportSubmissionsPage()
    {
        return $this->iAmOnPage('/admin\/documents\/list/');
    }

    public function iAmOnAdminAnalyticsPage()
    {
        return $this->iAmOnPage('/admin\/stats\/metrics$/');
    }

    public function iAmOnAdminNotificationPage()
    {
        return $this->iAmOnPage('/admin\/settings\/service-notification$/');
    }

    /**
     * @Then I should be on the admin stats reports page
     */
    public function iAmOnAdminStatsReportsPage()
    {
        return $this->iAmOnPage('/admin\/stats\/reports$/');
    }

    public function iAmOnAdminClientUnarchivePage()
    {
        return $this->iAmOnPage('/admin\/client\/.*\/unarchive.*$/');
    }

    public function iAmOnAdminClientArchivedPage()
    {
        return $this->iAmOnPage('/admin\/client\/.*\/archived.*$/');
    }
}
