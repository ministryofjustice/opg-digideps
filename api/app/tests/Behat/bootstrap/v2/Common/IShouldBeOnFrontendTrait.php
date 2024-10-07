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
     * @Then I should be on the report review page
     */
    public function iAmOnReportReviewPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/review$/');
    }

    /**
     * @Then I should be on the ndr review page
     */
    public function iAmOnNdrReviewPage(): bool
    {
        return $this->iAmOnPage('/ndr\/.*\/review$/');
    }

    /**
     * @Then I should be on the report declaration page
     */
    public function iAmOnReportDeclarationPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/declaration$/');
    }

    /**
     * @Then I should be on the ndr declaration page
     */
    public function iAmOnNdrDeclarationPage(): bool
    {
        return $this->iAmOnPage('/ndr\/.*\/declaration$/');
    }

    /**
     * @Then I should be on the report submitted page
     */
    public function iAmOnReportSubmittedPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/submitted$/');
    }

    /**
     * @Then I should be on the ndr submitted page
     */
    public function iAmOnNdrSubmittedPage(): bool
    {
        return $this->iAmOnPage('/ndr\/.*\/submitted$/');
    }

    /**
     * @Then I should be on the post-submission user research page
     */
    public function iAmOnPostSubmissionUserResearchPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/post_submission_user_research/');
    }

    /**
     * @Then I should be on the user research feedback submitted page
     */
    public function iAmOnUserResearchSubmittedPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/post_submission_user_research\/submitted$/');
    }

    /**
     * @Then I should be on the contacts summary page
     */
    public function iAmOnContactsSummaryPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/summary$/');
    }

    /**
     * @Then I should be on the add a contact page
     */
    public function iAmOnAddAContactPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/add/');
    }

    /**
     * @Then I should be on the contacts add another page
     */
    public function iAmOnContactsAddAnotherPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/add_another$/');
    }

    /**
     * @Then I should be on the additional information summary page
     */
    public function iAmOnAdditionalInformationSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/any-other-info\/summary\?from=last-step/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the financial decision actions page
     */
    public function iAmOnActionsPage1(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/actions\/step\/1$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the concerns actions page
     */
    public function iAmOnActionsPage2(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/actions\/step\/2$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the actions report summary page
     */
    public function iAmOnActionsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/actions\/summary.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I/they should be on the Lay homepage
     */
    public function iAmOnLayMainPage(): bool
    {
        return $this->iAmOnPage('/client\/.*/');
    }

    /**
     * @Then I should be on the Ndr Lay homepage
     */
    public function iAmOnNdrLayMainPage(): bool
    {
        return $this->iAmOnPage('/ndr$/');
    }

    /**
     * @Then I should be on the Lay reports overview page
     */
    public function iAmOnReportsOverviewPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/overview$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the documents summary page
     */
    public function iAmOnDocumentsSummaryPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/documents\/summary/');
    }

    /**
     * @Then I should be on the gifts exist page
     */
    public function iAmOnGiftsExistPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/exist.*$/');
    }

    /**
     * @Then I should be on the gifts summary page
     */
    public function iAmOnGiftsSummaryPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/summary$/');
    }

    /**
     * @Then I should be on the add a gift page
     */
    public function iAmOnGiftsAddPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/add.*$/');
    }

    /**
     * @Then I should be on the edit a gift page
     */
    public function iAmOnGiftsEditPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/edit\/.*$/');
    }

    /**
     * @Then I should be on the delete a gift page
     */
    public function iAmOnGiftsDeletionPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/.*\/delete$/');
    }

    /**
     * @Then I should be on the gifts start page
     */
    public function iAmOnGiftsStartPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts$/');
    }

    /**
     * @Then I should be on the live with client page
     */
    public function iAmOnVisitsCarePage1(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client receive paid care page
     */
    public function iAmOnVisitsCarePage2(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the who is doing the caring page
     */
    public function iAmOnVisitsCarePage3(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/3.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the does the client have a care plan page
     */
    public function iAmOnVisitsCarePage4(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/4.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the plans to move client page
     */
    public function iAmOnVisitsCarePage5(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/5.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the visits and care report summary page
     */
    public function iAmOnVisitsCareSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the accounts summary page
     */
    public function iAmOnAccountsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account.*\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the add another accounts page
     */
    public function iAmOnAccountsAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account.*\/add_another$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on add initial account page
     */
    public function iAmOnAccountsAddInitialPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/step1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on add account details page
     */
    public function iAmOnAccountsDetailsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/step2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on delete account details page
     */
    public function iAmOnAccountsDeletePage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/.*\/delete$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on account start page
     */
    public function iAmOnAccountsStartPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-accounts$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short category page
     */
    public function iAmOnMoneyOutShortCategoryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/category.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short exists page
     */
    public function iAmOnMoneyInShortOneOffPaymentsExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/oneOffPaymentsExist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short add page
     */
    public function iAmOnMoneyOutShortAddPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/add.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short edit page
     */
    public function iAmOnMoneyOutShortEditPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/edit.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short add another page
     */
    public function iAmOnMoneyOutShortAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/add_another.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short add another page
     */
    public function iAmOnMoneyInShortAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/add_another.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out short summary page
     */
    public function iAmOnMoneyOutShortSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out add payment page
     */
    public function iAmOnMoneyOutAddPaymentPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/step1.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out add payment details page
     */
    public function iAmOnMoneyOutAddPaymentDetailsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/step2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out add another payment page
     */
    public function iAmOnMoneyOutAddAnotherPaymentPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/add_another.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out summary page
     */
    public function iAmOnMoneyOutSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money out delete page
     */
    public function iAmOnMoneyOutDeletePage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/.*\/delete.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyTransfersExistPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/exist.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money transfers add page
     */
    public function iAmOnMoneyTransfersAddPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/step1.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money transfers add another page
     */
    public function iAmOnMoneyTransfersAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/add_another.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money transfers summary page
     */
    public function iAmOnMoneyTransfersSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/summary.*/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the any other info summary page
     */
    public function iAmOnAnyOtherInfoSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/any-other-info\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the lifestyle details page
     */
    public function iAmOnLifestyleDetailsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/step\/1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the lifestyle activities page
     */
    public function iAmOnLifestyleActivitiesPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/step\/2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the lifestyle summary page
     */
    public function iAmOnLifestyleSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnSpecifiedPage($specifiedUrlRegex): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/%s$/', $this->reportUrlPrefix, $specifiedUrlRegex));
    }

    /**
     * @Then I should be on the debts exist page
     */
    public function iAmOnDebtsExistPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/exist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the debts management page
     */
    public function iAmOnDebtsManagementPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/management.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the debts edit page
     */
    public function iAmOnDebtsEditPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/edit.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the debts summary page
     */
    public function iAmOnDebtsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsHowChargedPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/how-charged.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsPreviousReceivedExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/previous-received-exists.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsPreviousReceivedPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/previous-received.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs - costs received page
     */
    public function iAmOnDeputyCostsCostsReceievedPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/costs-received.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsInterimExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/interim-exists.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsInterimPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/interim.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs - SCCO amount page
     */
    public function iAmOnDeputyCostsAmountSccoPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/amount-scco.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs - breakdown page
     */
    public function iAmOnDeputyCostsBreakdownPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/breakdown.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate start page
     */
    public function iAmOnDeputyCostsEstimateStartPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate charges page
     */
    public function iAmOnDeputyCostsEstimateChargesPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/how-charged.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate breakdown page
     */
    public function iAmOnDeputyCostsEstimateBreakdownPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/breakdown.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate more info page
     */
    public function iAmOnDeputyCostsEstimateMoreInfoPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/more-info.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the deputy costs estimate summary page
     */
    public function iAmOnDeputyCostsEstimateSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the assets exist page
     */
    public function iAmOnAssetsExistPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/exist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the assets type page
     */
    public function iAmOnAssetTypePage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/step-type/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the add another asset page
     */
    public function iAmOnAddAnotherAssetPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/add_another/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the assets summary page
     */
    public function iAmOnAssetsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short summary page
     */
    public function iAmOnMoneyInShortSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short category page
     */
    public function iAmOnMoneyInShortCategoryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/category.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the money in short add page
     */
    public function iAmOnMoneyInShortAddPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/add.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the document upload page
     */
    public function iAmOnUploadDocumentPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/documents\/step\/2$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on client login page
     */
    public function iAmOnClientLoginPage()
    {
        return $this->iAmOnPage('/login$/');
    }

    /**
     * @Then I am on state benefits page
     */
    public function iAmOnStateBenefitsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I am on pensions and other income page
     */
    public function iAmOnStatePensionPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I am on other regular income page
     */
    public function iAmOnOtherRegularIncomePage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/3.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I am on damages and compensation page
     */
    public function iAmOnDamagesAndCompensationPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/4.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I am on one off payments page
     */
    public function iAmOnOneOffPaymentsPage()
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/5.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the income benefits summary page
     */
    public function iAmOnIncomeBenefitsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client benefits check summary page
     */
    public function iAmOnClientBenefitsCheckSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/summary.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client benefits check step 1 page
     */
    public function iAmOnClientBenefitsCheckStep1Page(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/step\/1.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client benefits check step 2 page
     */
    public function iAmOnClientBenefitsCheckStep2Page(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/step\/2.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client benefits check step 3 page
     */
    public function iAmOnClientBenefitsCheckStep3Page(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/step\/3.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client mental assessment page
     */
    public function iAmOnDecisionsPage2(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/mental-assessment.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the clients significant decision page
     */
    public function iAmOnDecisionsPage3(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/exist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the clients add decision page
     */
    public function iAmOnDecisionsPage4(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/add.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the clients add another decision page
     */
    public function iAmOnDecisionsPage5(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/add_another.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on the client decisions summary page
     */
    public function iAmOnDecisionsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnOrgSettingsPage()
    {
        return $this->iAmOnPage('/org\/settings.*$/');
    }

    public function iAmOnOrgUserAccountsPage()
    {
        return $this->iAmOnPage('/org\/settings\/organisation.*$/');
    }

    public function iAmOnOrgSettingsEditAnotherUserPage()
    {
        return $this->iAmOnPage('/org\/settings\/organisation\/.*\/edit\/.*.*$/');
    }

    public function iAmOnNoMoneyInExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in\/no-money-in-exists?.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnNoMoneyOutExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/no-money-out-exists?.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnNoMoneyInShortExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/no-money-in-short-exists?.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnNoMoneyOutShortExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/no-money-out-short-exists?.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnReUploadPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/reupload\/*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutShortOneOffPaymentsExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/oneOffPaymentsExist.*$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I/they should be on the Choose a Client homepage
     */
    public function iAmOnChooseAClientMainPage(): bool
    {
        return $this->iAmOnPage('/choose-a-client$/');
    }
}
