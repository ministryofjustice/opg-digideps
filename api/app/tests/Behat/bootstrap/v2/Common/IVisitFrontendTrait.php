<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait IVisitFrontendTrait
{
    /**
     * @When I visit the lay start page
     */
    public function iVisitLayStartPage(): void
    {
        $this->visitFrontendPath($this->getLayStartPageUrl());
    }

    /**
     * @When I visit the report overview page
     */
    public function iVisitReportOverviewPage(): void
    {
        $this->visitFrontendPath($this->getReportOverviewUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the report submitted page
     */
    public function iVisitReportSubmissionPage(): void
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
    public function iVisitMoneyOutShortSection(): void
    {
        $this->visitFrontendPath($this->getMoneyOutShortSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the short money out summary section
     */
    public function iVisitMoneyOutShortSummarySection(): void
    {
        $this->visitFrontendPath($this->getMoneyOutShortSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out report section
     */
    public function iVisitMoneyOutSection(): void
    {
        $this->visitFrontendPath($this->getMoneyOutSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out summary section
     */
    public function iVisitMoneyOutSummarySection(): void
    {
        $this->visitFrontendPath($this->getMoneyOutSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the accounts report section
     */
    public function iVisitAccountsSection(): void
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'bank-accounts');
        $this->visitFrontendPath($reportSectionUrl);
    }

    /**
     * @When I visit the accounts summary section
     */
    public function iVisitAccountsSummarySection(): void
    {
        $this->visitFrontendPath($this->getAccountsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle report section
     */
    public function iVisitHealthAndLifestyleSection(): void
    {
        $this->visitFrontendPath($this->getHealthAndLifestyleSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle summary section
     */
    public function iVisitHealthAndLifestyleSummarySection(): void
    {
        $this->visitFrontendPath($this->getHealthAndLifestyleSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the debts report section
     */
    public function iVisitDebtsSection(): void
    {
        $this->visitFrontendPath($this->getDebtsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the debts summary report section
     */
    public function iVisitDebtsSummarySection(): void
    {
        $this->visitFrontendPath($this->getDebtsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the assets report section
     */
    public function iVisitAssetsSection(): void
    {
        $this->visitFrontendPath($this->getAssetsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the assets summary report section
     */
    public function iVisitAssetsSummarySection(): void
    {
        $this->visitFrontendPath($this->getAssetsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the org dashboard page
     * @When they visit the org dashboard page
     */
    public function iVisitOrgDashboard(): void
    {
        $this->visitFrontendPath($this->getOrgDashboardUrl());
    }

    /**
     * @When I visit the deputy costs report section
     */
    public function iVisitDeputyCostsSection(): void
    {
        $this->visitFrontendPath($this->getDeputyCostsUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money in short report section
     */
    public function iVisitMoneyInShortSection(): void
    {
        $this->visitFrontendPath($this->getMoneyInShortSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the short money in summary section
     */
    public function iVisitMoneyInShortSummarySection(): void
    {
        $this->visitFrontendPath($this->getMoneyInShortSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the any other information report section
     */
    public function iVisitAnyOtherInfoSection(): void
    {
        $this->visitFrontendPath($this->getAnyOtherInfoUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the any other information summary report section
     */
    public function iVisitAnyOtherInfoSummarySection(): void
    {
        $this->visitFrontendPath($this->getAnyOtherInfoSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the actions report section
     */
    public function iVisitActionsSection(): void
    {
        $this->visitFrontendPath($this->getActionsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the actions summary report section
     */
    public function iVisitActionsSummarySection(): void
    {
        $this->visitFrontendPath($this->getActionsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the contacts report section
     */
    public function iVisitContactsSection(): void
    {
        $this->visitFrontendPath($this->getContactsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the contacts summary report section
     */
    public function iVisitTheContactsSummaryReportSection(): void
    {
        $this->visitFrontendPath($this->getContactsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the decisions report section
     */
    public function iVisitDecisionsSection(): void
    {
        $this->visitFrontendPath($this->getDecisionsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the decisions summary report section
     */
    public function iVisitTheDecisionsSummaryReportSection(): void
    {
        $this->visitFrontendPath($this->getDecisionsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy expenses report section
     */
    public function iVisitDeputyExpensesSection(): void
    {
        $this->visitFrontendPath($this->getDeputyExpensesSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy expenses summary report section
     */
    public function iVisitDeputyExpensesSummarySection(): void
    {
        $this->visitFrontendPath($this->getDeputyExpensesSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the documents report section
     */
    public function iVisitDocumentsSection(): void
    {
        $this->visitFrontendPath($this->getDocumentsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the documents summary report section
     */
    public function iVisitDocumentsSummarySection(): void
    {
        $this->visitFrontendPath($this->getDocumentsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the gifts report section
     */
    public function iVisitGiftsSection(): void
    {
        $this->visitFrontendPath($this->getGiftsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the gifts summary report section
     */
    public function iVisitGiftsSummarySection(): void
    {
        $this->visitFrontendPath($this->getGiftsSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money transfers report section
     */
    public function iVisitMoneyTransfersSection(): void
    {
        $this->visitFrontendPath($this->getMoneyTransfersSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the visits and care report section
     */
    public function iVisitVisitsAndCareSection(): void
    {
        $this->visitFrontendPath($this->getVisitsAndCareSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the visits and care summary report section
     */
    public function iVisitTheVisitAndCareSummaryReportSection(): void
    {
        $this->visitFrontendPath($this->getVisitsAndCareSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money in report section
     */
    public function iVisitMoneyInSection(): void
    {
        $this->visitFrontendPath($this->getMoneyInSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money in summary report section
     */
    public function iVisitMoneyInSummarySection(): void
    {
        $this->visitFrontendPath($this->getMoneyInSummarySectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy fees and expenses section
     */
    public function iVisitDeputyFeesAndExpensesSection(): void
    {
        $this->visitFrontendPath($this->getDeputyFeesAndExpensesSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the deputy costs estimate report section
     */
    public function iVisitDeputyCostsEstimateSection(): void
    {
        $this->visitFrontendPath($this->getDeputyCostsEstimateSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the client login page
     */
    public function iVisitTheClientLoginPage(): void
    {
        $this->visitFrontendPath($this->getClientLoginPageUrl());
    }

    /**
     * @When I visit the income benefits report section
     */
    public function iVisitIncomeBenefitsSection(): void
    {
        $this->visitFrontendPath($this->getIncomeBenefitsSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the income benefits summary section
     */
    public function iVisitIncomeBenefitsSummarySection(): void
    {
        $this->visitFrontendPath($this->getIncomeBenefitsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the client benefits check summary page
     */
    public function iVisitClientBenefitsCheckSummaryPage(): void
    {
        $this->visitFrontendPath($this->getClientBenefitsCheckSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @Given /^I visit the documents step 2 page$/
     */
    public function iVisitTheDocumentsStep2Page(): void
    {
        $this->visitFrontendPath($this->getDocumentsStep2Url($this->loggedInUserDetails->getPreviousReportId()));
    }

    /**
     * @Given /^I visit the activate user page for the user I am interacting with$/
     */
    public function iVisitTheActivateUserPageInteractingUser(): void
    {
        $activationToken = $this->em->getRepository(User::class)->findOneBy(
            ['email' => strtolower($this->interactingWithUserDetails->getUserEmail())]
        )->getRegistrationToken();

        $this->visitFrontendPath($this->getActivateUserUrl($activationToken));
    }

    /**
     * @Given /^I visit the frontend availability page$/
     */
    public function iVisitTheFrontendAvailabilityPage(): void
    {
        $this->visitFrontendPath($this->getServiceHealthUrl());
    }

    /**
     * @Given /^ visits the forgotten your password page$/
     */
    public function visitsTheForgottenYourPasswordPage(): void
    {
        $this->visitFrontendPath($this->getForgottenYourPasswordUrl());
    }

    /**
     * @Given /^I visit the organisation settings user account page for the logged in user$/
     */
    public function iVisitTheOrganisationSettingsUserAccountsPageForTheLoggedInUser(): void
    {
        $emailIdentifier = $this->loggedInUserDetails->getOrganisationEmailIdentifier();
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['emailIdentifier' => $emailIdentifier]);

        if (is_null($organisation)) {
            throw new BehatException(sprintf('Could not find an organisation with email identifier "%s"', $emailIdentifier));
        }

        $this->visitFrontendPath($this->getOrgSettingsUserAccountUrl(strval($organisation->getId())));
    }

    /**
     * @When I preview and check the report using the new template
     * @throws BehatException
     */
    public function iPreviewAndCheckTheReportUsingTheNewTemplate(): void
    {
        $reportId = $this->loggedInUserDetails?->getCurrentReportId();
        if ($reportId !== null) {
            $this->visitFrontendPath($this->getNewReportReviewUrl($reportId));
        } else {
            throw new BehatException('No active report ID found for the logged in user, cannot visit the new report review page');
        }
    }
}
