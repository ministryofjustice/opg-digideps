<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

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
        return $this->iAmOnPage('/report\/.*\/post_submission_user_research/');
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
        return $this->iAmOnPage(sprintf('/%s\/.*\/overview$/', $this->reportUrlPrefix));
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
     * @Then I should be on the live with client page
     */
    public function iAmOnVisitsCarePage1()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client receive paid care page
     */
    public function iAmOnVisitsCarePage2()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the who is doing the caring page
     */
    public function iAmOnVisitsCarePage3()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/3.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the does the client have a care plan page
     */
    public function iAmOnVisitsCarePage4()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/4.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the plans to move client page
     */
    public function iAmOnVisitsCarePage5()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/5.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the visits and care report summary page
     */
    public function iAmOnVisitsCareSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the accounts summary page
     */
    public function iAmOnAccountsSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account.*\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the add another accounts page
     */
    public function iAmOnAccountsAddAnotherPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account.*\/add_another$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on add initial account page
     */
    public function iAmOnAccountsAddInitialPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/step1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on add account details page
     */
    public function iAmOnAccountsDetailsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/step2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on delete account details page
     */
    public function iAmOnAccountsDeletePage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/.*\/delete$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on account start page
     */
    public function iAmOnAccountsStartPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-accounts$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the admin clients search page
     */
    public function iAmOnAdminClientsSearchPage()
    {
        return $this->iAmOnPage('/admin\/client\/se\arch$/');
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
     * @Then I should be on the money out short category page
     */
    public function iAmOnMoneyOutShortCategoryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/category$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short exists page
     */
    public function iAmOnMoneyOutShortExistsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/exist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short add page
     */
    public function iAmOnMoneyOutShortAddPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/add.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short edit page
     */
    public function iAmOnMoneyOutShortEditPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/edit.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short add another page
     */
    public function iAmOnMoneyOutShortAddAnotherPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/add_another.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short summary page
     */
    public function iAmOnMoneyOutShortSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/summary$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out add payment page
     */
    public function iAmOnMoneyOutAddPaymentPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/step1.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out add payment details page
     */
    public function iAmOnMoneyOutAddPaymentDetailsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/step2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out add another payment page
     */
    public function iAmOnMoneyOutAddAnotherPaymentPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/add_another.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out summary page
     */
    public function iAmOnMoneyOutSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out delete page
     */
    public function iAmOnMoneyOutDeletePage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/.*\/delete.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the any other info summary page
     */
    public function iAmOnAnyOtherInfoSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/any-other-info\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the lifestyle details page
     */
    public function iAmOnLifestyleDetailsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/step\/1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the lifestyle activities page
     */
    public function iAmOnLifestyleActivitiesPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/step\/2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the lifestyle summary page
     */
    public function iAmOnLifestyleSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnSpecifiedPage($specifiedUrlRegex)
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/%s$/', $this->reportUrlPrefix, $specifiedUrlRegex));
    }
}
