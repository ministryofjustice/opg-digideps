<?php

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

trait IShouldBeOnAdminTrait
{
    public function iAmOnAdminClientsSearchPage(): true
    {
        return $this->iAmOnPage('/admin\/client\/search$/');
    }

    public function iAmOnAdminClientDetailsPage(): true
    {
        return $this->iAmOnPage('/admin\/client\/.*\/details$/');
    }

    public function iAmOnAdminClientDischargePage(): true
    {
        return $this->iAmOnPage('/admin\/client\/.*\/discharge/');
    }

    public function iAmOnAdminUsersSearchPage(): true
    {
        return $this->iAmOnPage('/admin\//');
    }

    public function iAmOnAdminViewUserPage(): true
    {
        return $this->iAmOnPage('/admin\/user\/[0-9].*/');
    }

    public function iAmOnAdminAddUserPage(): true
    {
        return $this->iAmOnPage('/admin\/user-add$/');
    }

    public function iAmOnAdminEditUserPage(): true
    {
        return $this->iAmOnPage('/admin\/edit-user.*$/');
    }

    public function iAmOnAdminDeleteConfirmUserPage(): true
    {
        return $this->iAmOnPage('/admin\/delete-confirm\/[0-9].*$/');
    }

    public function iAmOnAdminOrganisationSearchPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\//');
    }

    public function iAmOnAdminOrganisationOverviewPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\/.*$/');
    }

    public function iAmOnAddUserToOrganisationPage(): true
    {
        return $this->iAmOnPage('/admin\/organisations\/.*\/add-user$/');
    }

    public function iAmOnAdminManageReportPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/manage$/');
    }

    public function iAmOnAdminReportChecklistPage(): true
    {
        return $this->iAmOnPage('/admin\/report\/.*\/checklist$/');
    }

    public function iAmOnAdminNotificationPage(): true
    {
        return $this->iAmOnPage('/admin\/settings\/service-notification$/');
    }
}
