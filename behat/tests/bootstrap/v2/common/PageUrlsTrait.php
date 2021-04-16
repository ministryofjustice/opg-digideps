<?php declare(strict_types=1);


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
    private string $duplicateClientFixtureUrl = '/admin/fixture/duplicate-client/%s';
    private string $courtOrdersFixtureUrl = '/admin/fixture/court-orders?%s';

    /**
     * @param int $reportId
     * @return string
     */
    public function getReportSubmittedUrl(int $reportId): string
    {
        return sprintf($this->reportSubmittedUrl, $reportId);
    }

    /**
     * @param int $reportId
     * @return string
     */
    public function getAccountsAddAnAccountUrl(int $reportId): string
    {
        return sprintf($this->accountsAddAnAccountUrl, $this->reportUrlPrefix, $reportId);
    }

    /**
     * @return string
     */
    public function getAccountsSummaryUrl(int $reportId): string
    {
        return sprintf($this->accountsSummaryUrl, $this->reportUrlPrefix, $reportId);
    }

    /**
     * @return string
     */
    public function getPostSubmissionUserResearchUrl(int $reportId): string
    {
        return sprintf($this->postSubmissionUserResearchUrl, $reportId);
    }

    /**
     * @param int $reportId
     * @return string
     */
    public function getUserResearchSubmittedUrl(int $reportId): string
    {
        return sprintf($this->userResearchSubmittedUrl, $reportId);
    }

    /**
     * @param int $reportId
     * @return string
     */
    public function getContactsSummaryUrl(int $reportId): string
    {
        return sprintf($this->contactsSummaryUrl, $reportId);
    }

    /**
     * @param int $reportId
     * @return string
     */
    public function getContactsAddUrl(int $reportId): string
    {
        return sprintf($this->contactsAddUrl, $reportId);
    }

    /**
     * @param int $reportId
     * @return string
     */
    public function getContactsAddAnotherUrl(int $reportId): string
    {
        return sprintf($this->contactsAddAnotherUrl, $reportId);
    }

    /**
     * @return string
     */
    public function getLayReportsOverviewUrl(): string
    {
        return $this->layReportsOverviewUrl;
    }

    /**
     * @return string
     */
    public function getAdminClientSearchUrl(): string
    {
        return $this->adminClientSearchUrl;
    }

    /**
     * @param int $clientId
     * @return string
     */
    public function getAdminClientDetailsUrl(int $clientId): string
    {
        return sprintf($this->adminClientDetailsUrl, $clientId);
    }

    /**
     * @param string $queryString
     * @return string
     */
    public function getCourtOrdersFixtureUrl(string $queryString): string
    {
        return sprintf($this->courtOrdersFixtureUrl, $queryString);
    }

    /**
     * @param int $clientId
     * @return string
     */
    public function getDuplicateClientFixtureUrl(int $clientId): string
    {
        return sprintf($this->duplicateClientFixtureUrl, $clientId);
    }
}
