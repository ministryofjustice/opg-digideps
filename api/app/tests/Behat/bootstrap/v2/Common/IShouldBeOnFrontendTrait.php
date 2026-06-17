<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait IShouldBeOnFrontendTrait
{
    public function iAmOnPage(string $urlRegex): true
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
     * @Then I should be on the confirm your details page
     */
    public function iAmOnReportConfirmDetailsPage(): bool
    {
        return $this->iAmOnPage('/report\/\d+\/confirm-details$/');
    }

    /**
     * @Then I should be on the report declaration page
     */
    public function iAmOnReportDeclarationPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/declaration$/');
    }

    public function iAmOnReportSubmittedPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/submitted$/');
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

    public function iAmOnAddAContactPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/contacts\/add/');
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
     *
     * @throws BehatException
     */
    public function iAmOnLayMainPage(): void
    {
        $currentUrl = $this->getCurrentUrl();

        // URL regexes matching the lay main page: a single court order, list of court orders, or waiting page
        $urlRegexes = ['/courtorder\/\d{1,12}$/', '/courtorder\/choose-a-court-order$/', '/courtorder\/waiting$/'];

        foreach ($urlRegexes as $urlRegex) {
            $onExpectedPage = preg_match($urlRegex, $currentUrl);
            if ($onExpectedPage) {
                break;
            }
        }

        if (!$onExpectedPage) {
            throw new BehatException(
                'Not on one of expected lay home pages. Current URL is: %s but expected URL regex is one of: ' .
                implode(', ', $urlRegexes)
            );
        }
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

    public function iAmOnGiftsExistPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/exist.*$/');
    }

    public function iAmOnGiftsSummaryPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/summary$/');
    }

    public function iAmOnGiftsAddPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/add.*$/');
    }

    public function iAmOnGiftsEditPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/edit\/.*$/');
    }

    public function iAmOnGiftsDeletionPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts\/.*\/delete$/');
    }

    public function iAmOnGiftsStartPage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/gifts$/');
    }

    public function iAmOnVisitsCarePage1(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/1.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnVisitsCarePage2(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/2.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnVisitsCarePage3(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/3.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnVisitsCarePage4(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/4.*$/', $this->reportUrlPrefix));
    }

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

    public function iAmOnAccountsAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account.*\/add_another$/', $this->reportUrlPrefix));
    }

    public function iAmOnAccountsAddInitialPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/step1.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnAccountsDetailsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/step2.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnAccountsDeletePage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-account\/.*\/delete$/', $this->reportUrlPrefix));
    }

    public function iAmOnAccountsStartPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/bank-accounts$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutShortCategoryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/category.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyInShortOneOffPaymentsExistsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/oneOffPaymentsExist.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutShortAddPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/add.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutShortEditPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/edit.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutShortAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/add_another.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyInShortAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/add_another.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutShortSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out-short\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutAddPaymentPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/step1.*/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutAddPaymentDetailsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/step2.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutAddAnotherPaymentPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/add_another.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyOutDeletePage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-out\/.*\/delete.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyTransfersExistPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/exist.*/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyTransfersAddPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/step1.*/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyTransfersAddAnotherPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/add_another.*/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyTransfersSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers\/summary.*/', $this->reportUrlPrefix));
    }

    public function iAmOnAnyOtherInfoSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/any-other-info\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnLifestyleDetailsPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/step\/1.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnLifestyleActivitiesPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/step\/2.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnLifestyleSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/lifestyle\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDebtsExistPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/exist.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDebtsManagementPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/management.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDebtsEditPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/debts\/edit.*$/', $this->reportUrlPrefix));
    }

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

    public function iAmOnDeputyCostsEstimateStartPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsEstimateChargesPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/how-charged.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDeputyCostsEstimateBreakdownPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/prof-deputy-costs-estimate\/breakdown.*$/', $this->reportUrlPrefix));
    }

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

    public function iAmOnAssetTypePage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/step-type/', $this->reportUrlPrefix));
    }

    public function iAmOnAssetsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/assets\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyInShortSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyInShortCategoryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/category.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnMoneyInShortAddPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/money-in-short\/add.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnUploadDocumentPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/documents\/step\/2$/', $this->reportUrlPrefix));
    }

    /**
     * @Then I should be on client login page
     */
    public function iAmOnClientLoginPage(): true
    {
        return $this->iAmOnPage('/login.*$/');
    }

    public function iAmOnStateBenefitsPage(): true
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/1.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnStatePensionPage(): true
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/2.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnOtherRegularIncomePage(): true
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/3.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDamagesAndCompensationPage(): true
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/4.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnOneOffPaymentsPage(): true
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/step\/5.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnIncomeBenefitsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/income-benefits\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnClientBenefitsCheckSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnClientBenefitsCheckStep1Page(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/step\/1.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnClientBenefitsCheckStep2Page(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/step\/2.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnClientBenefitsCheckStep3Page(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/client-benefits-check\/step\/3.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDecisionsPage2(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/mental-assessment.*$/', $this->reportUrlPrefix));
    }

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
     * @Then I should be on the clients edit decision page
     */
    public function iAmOnDecisionsPage5(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/edit.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnDecisionsSummaryPage(): bool
    {
        return $this->iAmOnPage(sprintf('/%s\/.*\/decisions\/summary.*$/', $this->reportUrlPrefix));
    }

    public function iAmOnOrgSettingsPage(): true
    {
        return $this->iAmOnPage('/org\/settings.*$/');
    }

    public function iAmOnOrgUserAccountsPage(): true
    {
        return $this->iAmOnPage('/org\/settings\/organisation.*$/');
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

    public function iAmOnYourDetailsPage(): true
    {
        return $this->iAmOnPage('/deputyship-details/');
    }

    public function iAmOnClientDetailsPage(): true
    {
        return $this->iAmOnPage('#deputyship-details/client#');
    }

    public function theyShouldBeOnTheAddYourClientPage(): true
    {
        return $this->iAmOnPage('#client/add$#');
    }

    /**
     * @Then /^I should be on the court order page$/
     */
    public function iShouldBeOnTheCourtOrderPage(): true
    {
        return $this->iAmOnPage('|/courtorder/\d+$|');
    }
}
