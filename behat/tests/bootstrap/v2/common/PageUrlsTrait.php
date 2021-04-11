<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait PageUrlsTrait
{
    private string $reportSubmittedUrl = '/report/%s/submitted';
    private string $postSubmissionUserResearchUrl = '/report/%s/post_submission_user_research';
    private string $userResearchSubmittedUrl = '/report/%s/post_submission_user_research/submitted';
    private string $contactsSummaryUrl = '/report/%s/contacts/summary';
    private string $contactsAddUrl = '/report/%s/contacts/add';
    private string $contactsAddAnotherUrl = '/report/%s/contacts/add_another';
    private string $accountsAddAnAccountUrl = 'report/%s/bank-account/step1';
    private string $layReportsOverviewUrl = '/lay';

    /**
     * @return string
     */
    public function getReportSubmittedUrl(int $reportId): string
    {
        return sprintf($this->reportSubmittedUrl, $reportId);
    }

    /**
     * @return string
     */
    public function getAccountsAddAnAccountUrl(int $reportId): string
    {
        return sprintf($this->accountsAddAnAccountUrl, $reportId);
    }

    /**
     * @return string
     */
    public function getPostSubmissionUserResearchUrl(int $reportId): string
    {
        return sprintf($this->postSubmissionUserResearchUrl, $reportId);
    }

    /**
     * @return string
     */
    public function getUserResearchSubmittedUrl(int $reportId): string
    {
        return sprintf($this->userResearchSubmittedUrl, $reportId);
    }

    /**
     * @return string
     */
    public function getContactsSummaryUrl(int $reportId): string
    {
        return sprintf($this->contactsSummaryUrl, $reportId);
    }

    /**
     * @return string
     */
    public function getContactsAddUrl(int $reportId): string
    {
        return sprintf($this->contactsAddUrl, $reportId);
    }

    /**
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
}
