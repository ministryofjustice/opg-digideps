<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait PageUrlsTrait
{
    // Frontend
    private string $accountsAddAnAccountUrl = '/%s/%s/bank-account/step1';
    private string $accountsSummaryUrl = '/%s/%s/bank-accounts/summary';
    private string $actionsSectionUrl = '/%s/%s/actions';
    private string $actionsSummarySectionUrl = '/%s/%s/actions/summary';
    private string $activateUserAccount = '/user/activate/%s';
    private string $anyOtherInfoUrl = '/%s/%s/any-other-info';
    private string $anyOtherInfoSummaryUrl = '/%s/%s/any-other-info/summary';
    private string $assetsSectionUrl = '/%s/%s/assets';
    private string $assetsSummarySectionUrl = '/%s/%s/assets/summary';
    private string $serviceHealthUrl = '/health-check/service';
    private string $clientLoginPageUrl = '/login';
    private string $clientBenefitCheckSummaryPageUrl = '/%s/%s/client-benefits-check/summary';
    private string $contactsAddUrl = '/report/%s/contacts/add';
    private string $contactsAddAnotherUrl = '/report/%s/contacts/add_another';
    private string $contactsSectionUrl = '/%s/%s/contacts';
    private string $contactsSummaryUrl = '/report/%s/contacts/summary';
    private string $debtsSectionUrl = '/%s/%s/debts';
    private string $debtsSummarySectionUrl = '/%s/%s/debts/summary';
    private string $decisionsSectionUrl = '/%s/%s/decisions';
    private string $decisionsSummarySectionUrl = '/%s/%s/decisions/summary';
    private string $deputyCostsUrl = '/report/%s/prof-deputy-costs';
    private string $deputyCostsEstimateSectionUrl = '/%s/%s/prof-deputy-costs-estimate';
    private string $deputyCostsSCCOAssessmentUrl = '/report/%s/prof-deputy-costs/amount-scco';
    private string $deputyExpensesSectionUrl = '/%s/%s/deputy-expenses';
    private string $deputyExpensesSummarySectionUrl = '/%s/%s/deputy-expenses/summary';
    private string $deputyFeesExpensesSectionUrl = '/report/%s/pa-fee-expense';
    private string $documentsSectionUrl = '/report/%s/documents';
    private string $documentsSummarySectionUrl = '/report/%s/documents/summary';
    private string $documentsStep2Url = '/%s/%s/documents/step/2';
    private string $documentsSubmitMoreUrl = '/%s/%s/documents/submit-more';
    private string $forgottenYourPasswordUrl = '/password-managing/forgotten';
    private string $giftsSectionUrl = '/%s/%s/gifts';
    private string $giftsSummarySectionUrl = '/%s/%s/gifts/summary';
    private string $healthAndLifestyleSectionUrl = '/%s/%s/lifestyle';
    private string $healthAndLifestyleSummaryUrl = '/%s/%s/lifestyle/summary';
    private string $incomeBenefitsSectionUrl = '/%s/%s/income-benefits';
    private string $incomeBenefitsSectionSummaryUrl = '/%s/%s/income-benefits/summary';
    private string $layStartPageUrl = '/';
    private string $moneyInSectionUrl = '/%s/%s/money-in';
    private string $moneyInSummarySectionUrl = '/%s/%s/money-in/summary';
    private string $moneyInShortSectionUrl = '/%s/%s/money-in-short';
    private string $moneyInShortSectionSummaryUrl = '/%s/%s/money-in-short/summary';
    private string $moneyOutSectionUrl = '/%s/%s/money-out';
    private string $moneyOutSectionSummaryUrl = '/%s/%s/money-out/summary';
    private string $moneyOutShortSectionUrl = '/%s/%s/money-out-short';
    private string $moneyOutShortSectionSummaryUrl = '/%s/%s/money-out-short/summary';
    private string $moneyTransfersSectionUrl = '/%s/%s/money-transfers';
    private string $orgSettingsUrl = '/org/settings';
    private string $orgSettingsUserAccountUrl = '/org/settings/organisation/%s';
    private string $orgAddUserUrl = '/org/settings/organisation/%s/add-user';
    private string $orgDashboardUrl = '/org';
    private string $postSubmissionUserResearchUrl = '/report/%s/post_submission_user_research';
    private string $reportOverviewUrl = '/%s/%s/overview';
    private string $reportSubmittedUrl = '/report/%s/submitted';
    private string $userResearchSubmittedUrl = '/report/%s/post_submission_user_research/submitted';
    private string $visitsAndCareSectionUrl = '/%s/%s/visits-care';
    private string $visitsAndCareSectionSummaryUrl = '/%s/%s/visits-care/summary';

    // Admin
    private string $adminUserSearchUrl = '/admin';
    private string $adminUploadLayUsersUrl = '/admin/pre-registration-upload';
    private string $adminClientArchivedUrl = '/admin/client/%s/archived';
    private string $adminClientDetailsUrl = '/admin/client/%s/details';
    private string $adminClientSearchUrl = '/admin/client/search';
    private string $adminSubmissionsPage = '/admin/documents/list';
    private string $adminEditUserUrl = '/admin/edit-user?filter=%s';
    private string $adminUploadOrgUsersUrl = '/admin/org-csv-upload';
    private string $adminChecklistPage = '/admin/report/%s/checklist';
    private string $adminNotificationUrl = '/admin/settings/service-notification';
    private string $adminDATReportUrl = '/admin/stats';
    private string $adminActiveLaysReportUrl = '/admin/stats/downloadActiveLaysCsv';
    private string $adminInactiveAdminUsersReportUrl = '/admin/stats/reports/inactive-admin-users-report';
    private string $adminAnalyticsUrl = '/admin/stats/metrics';
    private string $adminStatsReportsUrl = '/admin/stats/reports';
    private string $adminSatisfactionReportUrl = '/admin/stats/satisfaction';
    private string $adminUserResearchReportUrl = '/admin/stats/user-research';
    private string $adminAddUserUrl = '/admin/user-add';
    private string $adminViewUserUrl = '/admin/user/%s';
    private string $adminUploadUsersUrl = '/admin/upload';
    private string $adminMyUserProfileUrl = '/deputyship-details/your-details';

    // Fixtures
    private string $adminFixturesUrl = '/admin/fixtures';
    private string $courtOrdersFixtureUrl = '/admin/fixtures/court-orders?%s';

    // Admin Tools
    private string $adminToolsUrl = '/admin/tools';
    private string $reportReassignmentUrl = '/admin/tools/report-reassignment';

    public function getReportSubmittedUrl(int $reportId): string
    {
        return sprintf($this->reportSubmittedUrl, $reportId);
    }

    public function getAccountsAddAnAccountUrl(int $reportId): string
    {
        return sprintf($this->accountsAddAnAccountUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAccountsSummaryUrl(int $reportId): string
    {
        return sprintf($this->accountsSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getReportOverviewUrl(int $reportId): string
    {
        return sprintf($this->reportOverviewUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyOutShortSectionUrl(int $reportId): string
    {
        return sprintf($this->moneyOutShortSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyOutShortSectionSummaryUrl(int $reportId): string
    {
        return sprintf($this->moneyOutShortSectionSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyOutSectionUrl(int $reportId): string
    {
        return sprintf($this->moneyOutSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyOutSectionSummaryUrl(int $reportId): string
    {
        return sprintf($this->moneyOutSectionSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getPostSubmissionUserResearchUrl(int $reportId): string
    {
        return sprintf($this->postSubmissionUserResearchUrl, $reportId);
    }

    public function getUserResearchSubmittedUrl(int $reportId): string
    {
        return sprintf($this->userResearchSubmittedUrl, $reportId);
    }

    public function getContactsSummaryUrl(int $reportId): string
    {
        return sprintf($this->contactsSummaryUrl, $reportId);
    }

    public function getContactsAddUrl(int $reportId): string
    {
        return sprintf($this->contactsAddUrl, $reportId);
    }

    public function getContactsAddAnotherUrl(int $reportId): string
    {
        return sprintf($this->contactsAddAnotherUrl, $reportId);
    }

    public function getLayStartPageUrl(): string
    {
        return $this->layStartPageUrl;
    }

    public function getAdminClientSearchUrl(): string
    {
        return $this->adminClientSearchUrl;
    }

    public function getAdminClientDetailsUrl(int $clientId): string
    {
        return sprintf($this->adminClientDetailsUrl, $clientId);
    }

    public function getAdminClientArchivedUrl(int $clientId): string
    {
        return sprintf($this->adminClientArchivedUrl, $clientId);
    }

    public function getCourtOrdersFixtureUrl(string $queryString): string
    {
        return sprintf($this->courtOrdersFixtureUrl, $queryString);
    }

    public function getHealthAndLifestyleSectionUrl(int $reportId): string
    {
        return sprintf($this->healthAndLifestyleSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getHealthAndLifestyleSummaryUrl(int $reportId): string
    {
        return sprintf($this->healthAndLifestyleSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAdminAddUserPage(): string
    {
        return $this->adminAddUserUrl;
    }

    public function getAdminSearchUserPage(): string
    {
        return $this->adminUserSearchUrl;
    }

    public function getAdminViewUserPage(int $userId): string
    {
        return sprintf($this->adminViewUserUrl, $userId);
    }

    public function getAdminEditUserPage(int $userId): string
    {
        return sprintf($this->adminEditUserUrl, $userId);
    }

    public function getAdminMyUserProfilePage(): string
    {
        return $this->adminMyUserProfileUrl;
    }

    public function getAdminDATReportUrl(): string
    {
        return $this->adminDATReportUrl;
    }

    public function getAdminSatisfactionReportUrl(): string
    {
        return $this->adminSatisfactionReportUrl;
    }

    public function getAdminUserResearchReportUrl(): string
    {
        return $this->adminUserResearchReportUrl;
    }

    public function getAdminActiveLaysReportUrl(): string
    {
        return $this->adminActiveLaysReportUrl;
    }

    public function getInactiveAdminusersReportUrl(): string
    {
        return $this->adminInactiveAdminUsersReportUrl;
    }

    public function getAdminFixturesUrl(): string
    {
        return $this->adminFixturesUrl;
    }

    public function getAdminToolsUrl(): string
    {
        return $this->adminToolsUrl;
    }

    public function getAdminAnalyticsUrl(): string
    {
        return $this->adminAnalyticsUrl;
    }

    public function getAdminStatsReportsUrl(): string
    {
        return $this->adminStatsReportsUrl;
    }

    public function getDebtsSectionUrl(int $reportId): string
    {
        return sprintf($this->debtsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDebtsSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->debtsSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getOrgDashboardUrl(): string
    {
        return $this->orgDashboardUrl;
    }

    public function getDeputyCostsUrl(int $reportId): string
    {
        return sprintf($this->deputyCostsUrl, $reportId);
    }

    public function getDeputyCostsSCCOAssessmentUrl(int $reportId): string
    {
        return sprintf($this->deputyCostsSCCOAssessmentUrl, $reportId);
    }

    public function getAssetsSectionUrl(int $reportId): string
    {
        return sprintf($this->assetsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAssetsSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->assetsSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyInShortSectionUrl(int $reportId): string
    {
        return sprintf($this->moneyInShortSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyInShortSectionSummaryUrl(int $reportId): string
    {
        return sprintf($this->moneyInShortSectionSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAnyOtherInfoUrl(int $reportId): string
    {
        return sprintf($this->anyOtherInfoUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAnyOtherInfoSummaryUrl(int $reportId): string
    {
        return sprintf($this->anyOtherInfoSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getActionsSectionUrl(int $reportId): string
    {
        return sprintf($this->actionsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getActionsSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->actionsSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getContactsSectionUrl(int $reportId): string
    {
        return sprintf($this->contactsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDecisionsSectionUrl(int $reportId): string
    {
        return sprintf($this->decisionsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDecisionsSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->decisionsSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDeputyExpensesSectionUrl(int $reportId): string
    {
        return sprintf($this->deputyExpensesSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDeputyExpensesSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->deputyExpensesSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDocumentsSectionUrl(int $reportId): string
    {
        return sprintf($this->documentsSectionUrl, $reportId);
    }

    public function getDocumentsSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->documentsSummarySectionUrl, $reportId);
    }

    public function getGiftsSectionUrl(int $reportId): string
    {
        return sprintf($this->giftsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getGiftsSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->giftsSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyTransfersSectionUrl(int $reportId): string
    {
        return sprintf($this->moneyTransfersSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getVisitsAndCareSectionUrl(int $reportId): string
    {
        return sprintf($this->visitsAndCareSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getVisitsAndCareSectionSummaryUrl(int $reportId): string
    {
        return sprintf($this->visitsAndCareSectionSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyInSectionUrl(int $reportId): string
    {
        return sprintf($this->moneyInSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getMoneyInSummarySectionUrl(int $reportId): string
    {
        return sprintf($this->moneyInSummarySectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDeputyFeesAndExpensesSectionUrl(int $reportId): string
    {
        return sprintf($this->deputyFeesExpensesSectionUrl, $reportId);
    }

    public function getDeputyCostsEstimateSectionUrl(int $reportId): string
    {
        return sprintf($this->deputyCostsEstimateSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAdminUploadUsersUrl(): string
    {
        return $this->adminUploadUsersUrl;
    }

    public function getAdminUploadOrgUsersUrl(): string
    {
        return $this->adminUploadOrgUsersUrl;
    }

    public function getAdminUploadLayUsersUrl(): string
    {
        return $this->adminUploadLayUsersUrl;
    }

    public function getAdminSubmissionsPage(): string
    {
        return $this->adminSubmissionsPage;
    }

    public function getAdminNotificationUrl(): string
    {
        return $this->adminNotificationUrl;
    }

    public function getClientLoginPageUrl(): string
    {
        return $this->clientLoginPageUrl;
    }

    public function getIncomeBenefitsSectionUrl(int $reportId): string
    {
        return sprintf($this->incomeBenefitsSectionUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getIncomeBenefitsSummaryUrl(int $reportId): string
    {
        return sprintf($this->incomeBenefitsSectionSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getClientBenefitsCheckSummaryUrl(int $reportId): string
    {
        return sprintf($this->clientBenefitCheckSummaryPageUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getDocumentsStep2Url(int $reportId): string
    {
        return sprintf($this->documentsStep2Url, $this->reportUrlPrefix, $reportId);
    }

    public function getDocumentsSubmitMoreUrl(int $reportId): string
    {
        return sprintf($this->documentsSubmitMoreUrl, $this->reportUrlPrefix, $reportId);
    }

    public function getAdminChecklistUrl(string $reportId): string
    {
        return sprintf($this->adminChecklistPage, $reportId);
    }

    public function getActivateUserUrl(string $activationToken): string
    {
        return sprintf($this->activateUserAccount, $activationToken);
    }

    public function getServiceHealthUrl(): string
    {
        return $this->serviceHealthUrl;
    }

    public function getOrgAddUserUrl(string $orgId): string
    {
        return sprintf($this->orgAddUserUrl, $orgId);
    }

    public function getOrgSettingsUrl(): string
    {
        return $this->orgSettingsUrl;
    }

    public function getOrgSettingsUserAccountUrl(string $orgId): string
    {
        return sprintf($this->orgSettingsUserAccountUrl, $orgId);
    }

    public function getForgottenYourPasswordUrl(): string
    {
        return $this->forgottenYourPasswordUrl;
    }
}
