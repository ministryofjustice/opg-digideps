<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait IShouldBeOnTrait
{
    public function iAmOnPage(string $urlRegex)
    {
        $currentUrl = $this->getCurrentUrl();
        $onExpectedPage = preg_match($urlRegex, $currentUrl);

        if (!$onExpectedPage) {
            $this->throwContextualException(sprintf('Not on expected page. Current URL is: %s but expected URL regex is %s', $currentUrl, $urlRegex));
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
        return $this->iAmOnPage('/lay$/');
    }

    /**
     * @Then I should be on the Lay reports overview page
     */
    public function iAmOnReportsOverviewPage()
    {
        return $this->iAmOnPage('/report\/.*\/overview$/');
    }

    /**
     * @Then I should be on the documents summary page
     */
    public function iAmOnDocumentsSummaryPage()
    {
        return $this->iAmOnPage('/report\/.*\/documents\/summary/');
    }

    /**
     * @Then I should be on the gifts exist page
     */
    public function iAmOnGiftsExistPage()
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/exist.*$/');
    }

    /**
     * @Then I should be on the gifts summary page
     */
    public function iAmOnGiftsSummaryPage()
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/summary$/');
    }

    /**
     * @Then I should be on the add a gift page
     */
    public function iAmOnGiftsAddPage()
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/add.*$/');
    }

    /**
     * @Then I should be on the edit a gift page
     */
    public function iAmOnGiftsEditPage()
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/edit\/.*$/');
    }

    /**
     * @Then I should be on the delete a gift page
     */
    public function iAmOnGiftsDeletionPage()
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/.*\/delete$/');
    }

    /**
     * @Then I should be on the gifts start page
     */
    public function iAmOnGiftsStartPage()
    {
        return $this->iAmOnPage('/report\/.*\/gifts$/');
    }

    /**
     * @Then I should be on the visits and care first step page
     */
    public function iAmOnVisitsAndCareStep1Page()
    {
        return $this->iAmOnPage('/report\/.*\/visits-care\/step\/1$/');
    }

    /**
     * @Then I should be on the visits and care second step page
     */
    public function iAmOnVisitsAndCareStep2Page()
    {
        return $this->iAmOnPage('/report\/.*\/visits-care\/step\/2$/');
    }
}
