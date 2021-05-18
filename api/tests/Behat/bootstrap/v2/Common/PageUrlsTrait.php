<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait PageUrlsTrait
{
    // Frontend
    private string $reportSubmittedUrl = '/report/%s/submitted';
    private string $postSubmissionUserResearchUrl = '/report/%s/post_submission_user_research';
    private string $userResearchSubmittedUrl = '/report/%s/post_submission_user_research/submitted';
    private string $contactsSummaryUrl = '/report/%s/contacts/summary';
    private string $contactsAddUrl = '/report/%s/contacts/add';
    private string $contactsAddAnotherUrl = '/report/%s/contacts/add_another';
    private string $accountsAddAnAccountUrl = '%s/%s/bank-account/step1';
    private string $accountsSummaryUrl = '%s/%s/bank-account/summary';
    private string $layStartPageUrl = '/lay';
    private string $reportOverviewUrl = '/%s/%s/overview';
    private string $moneyOutShortSectionUrl = '%s/%s/money-out-short';
    private string $moneyOutShortSectionSummaryUrl = '%s/%s/money-out-short/summary';

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
}
