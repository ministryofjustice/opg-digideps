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
    }

    /**
     * @Then I should be on the report submitted page
     */
    public function iAmOnReportSubmittedPage()
    {
        $this->iAmOnPage('/report\/.*\/submitted$/');
    }

    /**
     * @Then I should be on the post-submission user research page
     */
    public function iAmOnPostSubmissionUserResearchPage()
    {
        $this->iAmOnPage('/report\/.*\/post_submission_user_research$/');
    }

    /**
     * @Then I should be on the user research feedback submitted page
     */
    public function iAmOnUserResearchSubmittedPage()
    {
        $this->iAmOnPage('/report\/.*\/post_submission_user_research\/submitted$/');
    }

    /**
     * @Then I should be on the contacts summary page
     */
    public function iAmOnContactsSummaryPage()
    {
        $this->iAmOnPage('/report\/.*\/contacts\/summary$/');
    }

    /**
     * @Then I should be on the add a contact page
     */
    public function iAmOnAddAContactPage()
    {
        $this->iAmOnPage('/report\/.*\/contacts\/add/');
    }

    /**
     * @Then I should be on the contacts add another page
     */
    public function iAmOnContactsAddAnotherPage()
    {
        $this->iAmOnPage('/report\/.*\/contacts\/add_another$/');
    }

    /**
     * @Then I should be on the Lay reports overview page
     */
    public function iAmOnReportsOverviewPage()
    {
        $this->iAmOnPage('/lay$/');
    }
}
