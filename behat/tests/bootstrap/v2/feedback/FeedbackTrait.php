<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Feedback;

use Exception;

trait FeedbackTrait
{
    /**
     * @Given a Lay Deputy completes and submits a report
     */
    public function aLayDeputyCompletesAndSubmitsAReport()
    {
        if (empty($this->layDeputyCompletedNotSubmittedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedNotSubmittedDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedNotSubmittedDetails->getEmail());
        $this->iSubmitTheReport();
    }

    /**
     * @Then I should be on the report submitted page
     */
    public function iShouldBeOnReportSubmittedPage()
    {
        $this->iAmOnPage('/report\/.*\/submitted$/');
    }

    /**
     * @Then I should be on the post-submission user research page
     */
    public function iShouldBePostSubmissionUserResearchPage()
    {
        $this->iAmOnPage('/report\/.*\/user-research/');
    }

    /**
     * @When I provide some post-submission feedback
     */
    public function iProvidePostSubmissionFeedback()
    {
        $this->selectOption('feedback_report[satisfactionLevel]', '5');
        $this->fillField('feedback_report[comments]', $this->faker->text(250));

        $this->pressButton('Send feedback');
    }
}
