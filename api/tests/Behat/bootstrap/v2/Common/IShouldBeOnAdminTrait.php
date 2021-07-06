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
}
