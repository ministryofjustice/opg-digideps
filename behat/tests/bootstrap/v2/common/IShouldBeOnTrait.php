<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait IShouldBeOnTrait
{
    public function iAmOnPage(string $urlRegex)
    {
        $currentUrl = $this->getCurrentUrl();
        $onExpectedPage = preg_match($urlRegex, $currentUrl);

        if (!$onExpectedPage) {
            $this->throwContextualException(sprintf('Not on expected page. Current URL is: %s', $currentUrl));
        }

        return true;
    }

    /**
     * @Then I should be on the report submitted page
     */
    public function iAmOnReportSubmittedPage()
    {
        return $this->iAmOnPage('/report\/.*\/submitted$/');
    }

    /**
     * @Then I should be on the post-submission user research page
     */
    public function iAmOnPostSubmissionUserResearchPage()
    {
        return $this->iAmOnPage('/report\/.*\/post_submission_user_research$/');
    }

    /**
     * @Then I should be on the user research feedback submitted page
     */
    public function iAmOnUserResearchSubmittedPage()
    {
        return $this->iAmOnPage('/report\/.*\/post_submission_user_research\/submitted$/');
    }

    /**
     * @Then I should be on the contacts summary page
     */
    public function iAmOnContactsSummaryPage()
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/summary$/');
    }

    /**
     * @Then I should be on the add a contact page
     */
    public function iAmOnAddAContactPage()
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/add/');
    }

    /**
     * @Then I should be on the contacts add another page
     */
    public function iAmOnContactsAddAnotherPage()
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/add_another$/');
    }

    /**
     * @Then I should be on the additional information summary page
     */
    public function iAmOnAdditionalInformationSummaryPage()
    {
        return $this->iAmOnPage('/report\/.*\/any-other-info\/summary\?from=last-step/');
    }

    /**
     * @Then I should be on the financial decision actions page
     */
    public function iAmOnActionsPage1()
    {
        $this->iAmOnPage('/report\/.*\/actions\/step\/1$/');
    }

    /**
     * @Then I should be on the concerns actions page
     */
    public function iAmOnActionsPage2()
    {
        $this->iAmOnPage('/report\/.*\/actions\/step\/2$/');
    }

    /**
     * @Then I should be on the actions report summary page
     */
    public function iAmOnActionsSummaryPage()
    {
        $this->iAmOnPage('/report\/.*\/actions\/summary.*/');
    }

    /**
     * @Then I should be on the Lay homepage
     */
    public function iAmOnLayMainPage()
    {
        $this->iAmOnPage('/lay$/');
    }

    /**
     * @Then I should be on the Lay reports overview page
     */
    public function iAmOnReportsOverviewPage()
    {
        return $this->iAmOnPage('/report\/.*\/overview$/');
    }
}
