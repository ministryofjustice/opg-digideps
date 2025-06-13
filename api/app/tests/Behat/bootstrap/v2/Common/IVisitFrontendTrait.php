<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Entity\Organisation;
use App\Entity\User;
use App\Tests\Behat\BehatException;

trait IVisitFrontendTrait
{
    /**
     * @When I visit the lay start page
     */
    public function iVisitLayStartPage()
    {
        $this->visitFrontendPath($this->getLayStartPageUrl());
    }

    /**
     * @When I visit the report overview page
     */
    public function iVisitReportOverviewPage()
    {
        $this->visitFrontendPath($this->getReportOverviewUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the report submitted page
     */
    public function iVisitReportSubmissionPage()
    {
        if (is_null($this->loggedInUserDetails->getPreviousReportId())) {
            throw new BehatException("Logged in user doesn't have a previous report ID associated with them. Try using a user that has submitted a report instead.");
        }

        $submittedReportUrl = $this->getReportSubmittedUrl($this->loggedInUserDetails->getPreviousReportId());
        $this->visitFrontendPath($submittedReportUrl);
    }

    /**
     * @When I visit the short money out report section
     */
    public function iVisitMoneyOutShortSection()
    {
        $this->visitFrontendPath($this->getMoneyOutShortSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the short money out summary section
     */
    public function iVisitMoneyOutShortSummarySection()
    {
        $this->visitFrontendPath($this->getMoneyOutShortSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out report section
     */
    public function iVisitMoneyOutSection()
    {
        $this->visitFrontendPath($this->getMoneyOutSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out summary section
     */
    public function iVisitMoneyOutSummarySection()
    {
        $this->visitFrontendPath($this->getMoneyOutSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the accounts report section
     */
    public function iVisitAccountsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'bank-accounts');
        $this->visitFrontendPath($reportSectionUrl);
    }

    /**
     * @When I visit the accounts summary section
     */
    public function iVisitAccountsSummarySection()
    {
        $this->visitFrontendPath($this->getAccountsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle report section
     */
    public function iVisitHealthAndLifestyleSection()
    {
        $this->visitFrontendPath($this->getHealthAndLifestyleSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle summary section
     */
    public function iVisitHealthAndLifestyleSummarySection()
    {
        $this->visitFrontendPath($this->getHealthAndLifestyleSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the debts report section
     */
    public function iVisitDebtsSection()
    {
        $this->visitFrontendPath($this->getDebtsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the debts summary report section
     */
    public function iVisitDebtsSummarySection()
    {
        $this->visitFrontendPath($this->getDebtsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the assets report section
     */
    public function iVisitAssetsSection()
    {
        $this->visitFrontendPath($this->getAssetsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the assets summary report section
     */
    public function iVisitAssetsSummarySection()
    {
        $this->visitFrontendPath($this->getAssetsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the org dashboard page
     */
    public function iVisitOrgDashboard()
    {
        $this->visitFrontendPath($this->getOrgDashboardUrl());
    }

    /**
     * @When I visit the deputy costs report section
     */
    public function iVisitDeputyCostsSection()
    {
        $this->visitFrontendPath($this->getDeputyCostsUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money in short report section
     */
    public function iVisitMoneyInShortSection()
    {
        $this->visitFrontendPath($this->getMoneyInShortSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the short money in summary section
     */
    public function iVisitMoneyInShortSummarySection()
    {
        $this->visitFrontendPath($this->getMoneyInShortSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the any other information report section
     */
    public function iVisitAnyOtherInfoSection()
    {
        $this->visitFrontendPath($this->getAnyOtherInfoUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the any other information summary report section
     */
    public function iVisitAnyOtherInfoSummarySection()
    {
        $this->visitFrontendPath($this->getAnyOtherInfoSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the actions report section
     */
    public function iVisitActionsSection()
    {
        $this->visitFrontendPath($this->getActionsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the actions summary report section
     */
    public function iVisitActionsSummarySection()
    {
        $this->visitFrontendPath($this->getActionsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the contacts report section
     */
    public function iVisitContactsSection()
    {
        $this->visitFrontendPath($this->getContactsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the contacts summary report section
     */
    public function iVisitTheContactsSummaryReportSection()
    {
        $this->visitFrontendPath($this->getContactsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the decisions report section
     */
    public function iVisitDecisionsSection()
    {
        $this->visitFrontendPath($this->getDecisionsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the decisions summary report section
     */
    public function iVisitTheDecisionsSummaryReportSection()
    {
        $this->visitFrontendPath($this->getDecisionsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy expenses report section
     */
    public function iVisitDeputyExpensesSection()
    {
        $this->visitFrontendPath($this->getDeputyExpensesSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy expenses summary report section
     */
    public function iVisitDeputyExpensesSummarySection()
    {
        $this->visitFrontendPath($this->getDeputyExpensesSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the documents report section
     */
    public function iVisitDocumentsSection()
    {
        $this->visitFrontendPath($this->getDocumentsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the documents summary report section
     */
    public function iVisitDocumentsSummarySection()
    {
        $this->visitFrontendPath($this->getDocumentsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the gifts report section
     */
    public function iVisitGiftsSection()
    {
        $this->visitFrontendPath($this->getGiftsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the gifts summary report section
     */
    public function iVisitGiftsSummarySection()
    {
        $this->visitFrontendPath($this->getGiftsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money transfers report section
     */
    public function iVisitMoneyTransfersSection()
    {
        $this->visitFrontendPath($this->getMoneyTransfersSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the visits and care report section
     */
    public function iVisitVisitsAndCareSection()
    {
        $this->visitFrontendPath($this->getVisitsAndCareSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the visits and care summary report section
     */
    public function iVisitTheVisitAndCareSummaryReportSection()
    {
        $this->visitFrontendPath($this->getVisitsAndCareSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money in report section
     */
    public function iVisitMoneyInSection()
    {
        $this->visitFrontendPath($this->getMoneyInSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money in summary report section
     */
    public function iVisitMoneyInSummarySection()
    {
        $this->visitFrontendPath($this->getMoneyInSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy fees and expenses section
     */
    public function iVisitDeputyFeesAndExpensesSection()
    {
        $this->visitFrontendPath($this->getDeputyFeesAndExpensesSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy costs estimate report section
     */
    public function iVisitDeputyCostsEstimateSection()
    {
        $this->visitFrontendPath($this->getDeputyCostsEstimateSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the client login page
     */
    public function iVisitTheClientLoginPage()
    {
        $this->visitFrontendPath($this->getClientLoginPageUrl());
    }

    /**
     * @When I visit the income benefits report section
     */
    public function iVisitIncomeBenefitsSection()
    {
        $this->visitFrontendPath($this->getIncomeBenefitsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the income benefits summary section
     */
    public function iVisitIncomeBenefitsSummarySection()
    {
        $this->visitFrontendPath($this->getIncomeBenefitsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the client benefits check summary page
     */
    public function iVisitClientBenefitsCheckSummaryPage()
    {
        $this->visitFrontendPath($this->getClientBenefitsCheckSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @Given /^I visit the documents step 2 page$/
     */
    public function iVisitTheDocumentsStep2Page()
    {
        $this->visitFrontendPath($this->getDocumentsStep2Url($this->loggedInUserDetails->getPreviousReportId()));
    }

    /**
     * @Given /^I visit the activate user page for the user I am interacting with$/
     */
    public function iVisitTheActivateUserPageInteractingUser()
    {
        $activationToken = $this->em->getRepository(User::class)->findOneBy(
            ['email' => strtolower($this->interactingWithUserDetails->getUserEmail())]
        )->getRegistrationToken();

        $this->visitFrontendPath($this->getActivateUserUrl($activationToken));
    }

    /**
     * @Given /^I visit the frontend availability page$/
     */
    public function iVisitTheFrontendAvailabilityPage()
    {
        $this->visitFrontendPath($this->getServiceHealthUrl());
    }

    /**
     * @Given /^ visits the forgotten your password page$/
     */
    public function visitsTheForgottenYourPasswordPage()
    {
        $this->visitFrontendPath($this->getForgottenYourPasswordUrl());
    }

    /**
     * @Given /^I visit the organisation settings user account page for the logged in user$/
     */
    public function iVisitTheOrganisationSettingsUserAccountsPageForTheLoggedInUser()
    {
        $emailIdentifier = $this->loggedInUserDetails->getOrganisationEmailIdentifier();
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['emailIdentifier' => $emailIdentifier]);

        if (is_null($organisation)) {
            throw new BehatException(sprintf('Could not find an organisation with email identifier "%s"', $emailIdentifier));
        }

        $this->visitFrontendPath($this->getOrgSettingsUserAccountUrl(strval($organisation->getId())));
    }
}
