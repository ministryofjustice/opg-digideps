<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait PageUrlsTrait
{
    // Frontend
    private string $accountsAddAnAccountUrl = '/%s/%s/bank-account/step1';
    private string $accountsSummaryUrl = '/%s/%s/bank-account/summary';
    private string $assetsSectionUrl = '/%s/%s/assets';
    private string $assetsSummarySectionUrl = '/%s/%s/assets/summary';
    private string $contactsAddUrl = '/report/%s/contacts/add';
    private string $contactsAddAnotherUrl = '/report/%s/contacts/add_another';
    private string $contactsSummaryUrl = '/report/%s/contacts/summary';
    private string $debtsSectionUrl = '/%s/%s/debts';
    private string $debtsSummarySectionUrl = '/%s/%s/debts/summary';
    private string $deputyCostsUrl = '/report/%s/prof-deputy-costs';
    private string $deputyCostsSCCOAssessmentUrl = '/report/%s/prof-deputy-costs/amount-scco';
    private string $healthAndLifestyleSectionUrl = '/%s/%s/lifestyle';
    private string $healthAndLifestyleSummaryUrl = '/%s/%s/lifestyle/summary';
    private string $layStartPageUrl = '/lay';
    private string $moneyOutSectionUrl = '/%s/%s/money-out';
    private string $moneyOutSectionSummaryUrl = '/%s/%s/money-out/summary';
    private string $moneyOutShortSectionUrl = '/%s/%s/money-out-short';
    private string $moneyOutShortSectionSummaryUrl = '/%s/%s/money-out-short/summary';
    private string $orgDashboardUrl = '/org';
    private string $postSubmissionUserResearchUrl = '/report/%s/post_submission_user_research';
    private string $reportOverviewUrl = '/%s/%s/overview';
    private string $reportSubmittedUrl = '/report/%s/submitted';
    private string $userResearchSubmittedUrl = '/report/%s/post_submission_user_research/submitted';
    private string $moneyInShortSectionUrl = '/%s/%s/money-in-short';
    private string $moneyInShortSectionSummaryUrl = '/%s/%s/money-in-short/summary';

    // Admin
    private string $adminClientSearchUrl = '/admin/client/search';
    private string $adminClientDetailsUrl = '/admin/client/%s/details';
    private string $adminAddUserUrl = '/admin/user-add';
    private string $adminViewUserUrl = '/admin/user/%s';
    private string $adminEditUserUrl = '/admin/edit-user?filter=%s';
    private string $adminMyUserProfileUrl = '/deputyship-details/your-details';
    private string $adminAnalyticsUrl = '/admin/stats/metrics';
    private string $adminDATReportUrl = '/admin/stats';
    private string $adminSatisfactionReportUrl = '/admin/stats/satisfaction';
    private string $adminUserResearchReportUrl = '/admin/stats/user-research';
    private string $adminActiveLaysReportUrl = '/admin/stats/downloadActiveLaysCsv';
    private string $adminFixturesUrl = '/admin/fixtures';

    // Fixtures
    private string $courtOrdersFixtureUrl = '/admin/fixtures/court-orders?%s';

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

    public function getAdminFixturesUrl(): string
    {
        return $this->adminFixturesUrl;
    }

    public function getAdminAnalyticsUrl(): string
    {
        return $this->adminAnalyticsUrl;
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
}
