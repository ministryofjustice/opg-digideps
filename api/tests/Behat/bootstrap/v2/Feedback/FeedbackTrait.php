<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Feedback;

trait FeedbackTrait
{
    /**
     * @When I provide some post-submission feedback
     */
    public function iProvidePostSubmissionFeedback()
    {
        try {
            $this->iAmOnReportSubmittedPage();
        } catch (\Throwable $e) {
            $loggedInUserPreviousReportId = $this->loggedInUserDetails->getPreviousReportId();
            $reportSubmittedUrl = $this->getReportSubmittedUrl($loggedInUserPreviousReportId);
            $this->visitFrontendPath($reportSubmittedUrl);

            if (!str_contains($this->getCurrentUrl(), $reportSubmittedUrl)) {
                $this->throwContextualException(sprintf("Couldn't access report submitted page for current user. Current url: %s", $this->getCurrentUrl()));
            }
        }

        $this->selectOption('feedback_report[satisfactionLevel]', '5');
        $this->fillField('feedback_report[comments]', $this->faker->text(250));

        $this->pressButton('Send feedback');
    }

    /**
     * @When I provide valid user research responses
     */
    public function iProvideValidUserResearchResponses()
    {
        try {
            $this->iAmOnPostSubmissionUserResearchPage();
        } catch (\Throwable $e) {
            $submittedReportId = $this->loggedInUserDetails->getPreviousReportId();
            $postSubmissionURUrl = $this->getPostSubmissionUserResearchUrl($submittedReportId);
            $this->visitFrontendPath($postSubmissionURUrl);

            if (!str_contains($this->getCurrentUrl(), $postSubmissionURUrl)) {
                $this->throwContextualException(sprintf("Couldn't access post submission user research page for current user. Current url: %s", $this->getCurrentUrl()));
            }
        }

        $this->selectOption('user_research_response[deputyshipLength]', 'underOne');

        $this->checkOption('user_research_response_agreedResearchTypes_0');
        $this->checkOption('user_research_response_agreedResearchTypes_1');
        $this->checkOption('user_research_response_agreedResearchTypes_2');

        $this->fillField('user_research_response[hasAccessToVideoCallDevice]', 'yes');

        $this->pressButton('Submit');
    }
}
