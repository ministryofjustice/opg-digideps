<?php

namespace App\Tests\Behat\v2\Common;

trait IVisitAdminTrait
{
    /**
     * @When I visit the admin clients search page
     */
    public function iVisitAdminClientSearchPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            $this->throwContextualException(
                'Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead'
            );
        }

        $this->visitAdminPath($this->getAdminClientSearchUrl());
    }

    /**
     * @When I visit the admin client details page for an existing client linked to a Lay deputy
     */
    public function iVisitAdminLayClientDetailsPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            $this->throwContextualException(
                'Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead'
            );
        }

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->layDeputySubmittedDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);

        $this->interactingWithUserDetails = $this->layDeputySubmittedDetails;
    }

    /**
     * @When I visit the admin client details page for an existing client linked to a deputy in an Organisation
     */
    public function iVisitAdminOrgClientDetailsPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            $this->throwContextualException(
                'Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead'
            );
        }

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->profAdminDeputySubmittedDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);

        $this->interactingWithUserDetails = $this->profAdminDeputySubmittedDetails;
    }

    /**
     * @When I visit the admin Add Users page
     */
    public function iVisitAdminAddUserPage()
    {
        $this->visitAdminPath($this->getAdminAddUserPage());
    }

    /**
     * @When I visit the admin Edit User page for the user I'm interacting with
     */
    public function iVisitAdminEditUserPageForInteractingWithUser()
    {
        $this->assertInteractingWithUserIsSet();

        $this->visitAdminPath(
            $this->getAdminEditUserPage($this->interactingWithUserDetails->getUserId())
        );
    }

    /**
     * @When I visit my user profile page
     */
    public function iVisitMyUserProfilePage()
    {
        $this->visitAdminPath($this->getAdminMyUserProfilePage());
    }
}
