<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Common;

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
    private string $layReportsOverviewUrl = '/lay';

    // Admin
    private string $adminClientSearchUrl = '/admin/client/search';
    private string $adminClientDetailsUrl = '/admin/client/%s/details';

    // Fixtures
    private string $courtOrdersFixtureUrl = '/admin/fixture/court-orders?%s';

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

    public function getLayReportsOverviewUrl(): string
    {
        return $this->layReportsOverviewUrl;
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
}
