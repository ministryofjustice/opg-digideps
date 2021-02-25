<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait IShouldBeOnTrait
{
    /**
     * @Then I should be on the report submitted page
     */
    public function iShouldBeOnReportSubmittedPage()
    {
        $this->iAmOnPage('/report\/.*\/submitted$/');
    }

    /**
     * @Then I should be on the post-submission user research page
     */
    public function iShouldBePostSubmissionUserResearchPage()
    {
        $this->iAmOnPage('/report\/.*\/user-research/');
    }

    /**
     * @Then I should be on the contacts summary page
     */
    public function iShouldBeOnContactsSummaryPage()
    {
        $this->iAmOnPage('/report\/.*\/contacts\/summary$/');
    }

    /**
     * @Then I should be on the add a contact page
     */
    public function iShouldBeOnAddAContactPage()
    {
        $this->iAmOnPage('/report\/.*\/contacts\/add/');
    }

    /**
     * @Then I should be on the contacts add another page
     */
    public function iShouldBeOnContactsAddAnotherPage()
    {
        $this->iAmOnPage('/report\/.*\/contacts\/add_another$/');
    }

    /**
     * @Then I should be on the Lay reports overview page
     */
    public function iShouldBeOnReportsOverviewPage()
    {
        $this->iAmOnPage('/lay$/');
    }
}
