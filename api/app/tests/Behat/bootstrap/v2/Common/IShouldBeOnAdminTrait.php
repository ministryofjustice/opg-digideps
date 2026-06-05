<?php

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

trait IShouldBeOnAdminTrait
{
    /**
     * @Then I should be on the admin clients search page
     */
    public function iAmOnAdminClientsSearchPage(): true
    {
        return $this->iAmOnPage('/admin\/client\/search$/');
    }

    /**
     * @Then I should be on the admin client details page
     */
    public function iAmOnAdminClientDetailsPage(): true
    {
        return $this->iAmOnPage('/admin\/client\/.*\/details$/');
    }

    /**
     * @Then I should be on the admin client discharge page
     */
    public function iAmOnAdminClientDischargePage(): true
    {
        return $this->iAmOnPage('/admin\/client\/.*\/discharge/');
    }

    /**
     * @Then I should be on the admin users search page
     */
    public function iAmOnAdminUsersSearchPage(): true
    {
        return $this->iAmOnPage('/admin\//');
    }

    /**
     * @Then I should be on the admin upload users page
     */
    public function iAmOnAdminUploadUsersPage(): true
    {
        return $this->iAmOnPage('/admin\/upload/');
    }

    /**
     * @Then I should be on the admin org csv upload page
     */
    public function iAmOnAdminOrgCsvUploadPage(): true
    {
        return $this->iAmOnPage('/admin\/org-csv-upload/');
    }

    /**
     * @Then I should be on the admin lay csv upload page
     */
    public function iAmOnAdminLayCsvUploadPage(): true
    {
        return $this->iAmOnPage('/admin\/pre-registration-upload/');
    }

    /**
     * @Then I should be on the admin view user page
     */
    public function iAmOnAdminViewUserPage(): true
    {
        return $this->iAmOnPage('/admin\/user\/[0-9].*/');
    }

    /**
     * @Then I should be on the admin add user page
     */
    public function iAmOnAdminAddUserPage(): true
    {
        return $this->iAmOnPage('/admin\/user-add$/');
    }

    /**
     * @Then I should be on the admin edit user page
     */
    public function iAmOnAdminEditUserPage(): true
    {
        return $this->iAmOnPage('/admin\/edit-user.*$/');
    }

    /**
     * @Then I should be on the admin delete confirm user page
     */
    public function iAmOnAdminDeleteConfirmUserPage(): true
    {
        return $this->iAmOnPage('/admin\/delete-confirm\/[0-9].*$/');
    }

    /**
     * @Then I should be on the admin organisation search page
     */
    public function iAmOnAdminOrganisationSearchPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\//');
    }

    /**
     * @Then I should be on the admin add organisation page
     */
    public function iAmOnAdminAddOrganisationPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\/add$/');
    }

    /**
     * @Then I should be on the admin organisation overview page
     */
    public function iAmOnAdminOrganisationOverviewPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\/.*$/');
    }

    /**
     * @Then I should be on the add user to organisation page
     */
    public function iAmOnAddUserToOrganisationPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\/.*\/add-user$/');
    }

    /**
     * @Then I should be on the admin stats page
     */
    public function iAmOnAdminStatsPage(): true
    {
        return $this->iAmOnPage('/admin\/stats$/');
    }

    /**
     * @Then I should be on the admin stats user research page
     */
    public function iAmOnAdminStatsUserResearchPage(): true
    {
        return $this->iAmOnPage('/admin\/stats\/user-research$/');
    }

    /**
     * @Then I should be on the admin stats satisfaction page
     */
    public function iAmOnAdminStatsSatisfactionPage(): true
    {
        return $this->iAmOnPage('/admin\/stats\/satisfaction$/');
    }

    public function iAmOnAdminManageReportPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage$/');
    }

    public function iAmOnAdminManageReportConfirmPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage-confirm$/');
    }

    public function iAmOnAdminManageCloseReportConfirmPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage-close-report-confirm$/');
    }

    /**
     * @Then I should be on the admin report checklist page
     */
    public function iAmOnAdminReportChecklistPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/checklist$/');
    }

    /**
     * @Then I should be on the admin report checklist submitted page
     */
    public function iAmOnAdminReportChecklistSubmittedPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/checklist-submitted$/');
    }

    public function iAmOnAdminReportSubmissionsPage(): true
    {
        return $this->iAmOnPage('/admin\/documents\/list/');
    }

    public function iAmOnAdminAnalyticsPage(): true
    {
        return $this->iAmOnPage('/admin\/stats\/metrics$/');
    }

    public function iAmOnAdminNotificationPage(): true
    {
        return $this->iAmOnPage('/admin\/settings\/service-notification$/');
    }

    /**
     * @Then I should be on the admin stats reports page
     */
    public function iAmOnAdminStatsReportsPage(): true
    {
        return $this->iAmOnPage('/admin\/stats\/reports$/');
    }

    public function iAmOnAdminClientUnarchivePage(): true
    {
        return $this->iAmOnPage('/admin\/client\/.*\/unarchive.*$/');
    }

    public function iAmOnAdminClientArchivedPage(): true
    {
        return $this->iAmOnPage('/admin\/client\/.*\/archived.*$/');
    }
}
