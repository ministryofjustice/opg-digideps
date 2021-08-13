<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;

trait IShouldBeOnFrontendTrait
{
    public function iAmOnPage(string $urlRegex)
    {
        $currentUrl = $this->getCurrentUrl();
        $onExpectedPage = preg_match($urlRegex, $currentUrl);

        if (!$onExpectedPage) {
            throw new BehatException(sprintf('Not on expected page. Current URL is: %s but expected URL regex is %s', $currentUrl, $urlRegex));
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

    /**
     * @Then I should be on the debts exist page
     */
    public function iAmOnDebtsExistPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/exist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the debts management page
     */
    public function iAmOnDebtsManagementPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/management.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the debts edit page
     */
    public function iAmOnDebtsEditPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/edit.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the debts summary page
     */
    public function iAmOnDebtsSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsHowChargedPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/how-charged.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsPreviousReceivedExistsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/previous-received-exists.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsPreviousReceivedPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/previous-received.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs - costs received page
     */
    public function iAmOnDeputyCostsCostsReceievedPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/costs-received.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsInterimExistsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/interim-exists.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsInterimPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/interim.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs - SCCO amount page
     */
    public function iAmOnDeputyCostsAmountSccoPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/amount-scco.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs - breakdown page
     */
    public function iAmOnDeputyCostsBreakdownPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/breakdown.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate start page
     */
    public function iAmOnDeputyCostsEstimateStartPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate charges page
     */
    public function iAmOnDeputyCostsEstimateChargesPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/how-charged.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate breakdown page
     */
    public function iAmOnDeputyCostsEstimateBreakdownPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/breakdown.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate more info page
     */
    public function iAmOnDeputyCostsEstimateMoreInfoPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/more-info.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate summary page
     */
    public function iAmOnDeputyCostsEstimateSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the assets exist page
     */
    public function iAmOnAssetsExistPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/exist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the assets type page
     */
    public function iAmOnAssetTypePage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/step-type/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the add another asset page
     */
    public function iAmOnAddAnotherAssetPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/add_another/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the assets summary page
     */
    public function iAmOnAssetsSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short summary page
     */
    public function iAmOnMoneyInShortSummaryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short category page
     */
    public function iAmOnMoneyInShortCategoryPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/category.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short add page
     */
    public function iAmOnMoneyInShortAddPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/add.*$/', $this->reportUrlPrefix));
    }
}
