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
        $this->visit($submittedReportUrl);
    }
}
