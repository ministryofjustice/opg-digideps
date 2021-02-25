<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Feedback;

use Exception;

trait FeedbackTrait
{
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
