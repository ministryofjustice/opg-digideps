<?php


namespace DigidepsBehat\v2\Common;

trait IVisitAdminTrait
{
    /**
     * @When I visit the clients search page
     */
    public function iVisitClientSearchPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            $this->throwContextualException(
                "Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead"
            );
        }

        $this->visitAdminPath($this->getAdminClientSearchUrl());
    }

    /**
     * @When I visit the client details page for an existing client linked to a Lay deputy
     */
    public function iVisitClientDetailsPage()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), $this->loggedInUserDetails::ADMIN_ROLES)) {
            $this->throwContextualException(
                "Attempting to access an admin page as a non-admin user. Try logging in as an admin user instead"
            );
        }

        $clientDetailsUrl = $this->getAdminClientDetailsUrl($this->layDeputySubmittedDetails->getClientId());
        $this->visitAdminPath($clientDetailsUrl);

        $this->interactingWithUserDetails = $this->layDeputySubmittedDetails;
    }
}
