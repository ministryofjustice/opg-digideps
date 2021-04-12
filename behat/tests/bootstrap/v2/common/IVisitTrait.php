<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait IVisitTrait
{
    /**
     * @When I visit the report submitted page
     */
    public function iVisitReportSubmissionPage()
    {
        if (is_null($this->loggedInUserDetails->getPreviousReportId())) {
            $this->throwContextualException(
                "Logged in user doesn't have a previous report ID associated with them. Try using a user that has submitted a report instead."
            );
        }

        $submittedReportUrl = $this->getReportSubmittedUrl($this->loggedInUserDetails->getPreviousReportId());
        $this->visitFrontendPath($submittedReportUrl);
    }

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
}
