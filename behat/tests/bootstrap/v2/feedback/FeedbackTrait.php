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
        if (empty($this->layDeputyCompletedNotSubmittedEmail)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedNotSubmittedEmail');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedNotSubmittedEmail);
        $this->submitReport();
    }
}
