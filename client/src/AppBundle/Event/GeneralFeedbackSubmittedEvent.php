<?php declare(strict_types=1);


namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class GeneralFeedbackSubmittedEvent extends Event
{
    public const NAME = 'general.feedback.submitted';

    /** @var array */
    private $feedbackFormResponse;

    /**
     * @return array
     */
    public function getFeedbackFormResponse(): array
    {
        return $this->feedbackFormResponse;
    }

    /**
     * @param array $feedbackFormResponse
     * @return GeneralFeedbackSubmittedEvent
     */
    public function setFeedbackFormResponse(array $feedbackFormResponse): GeneralFeedbackSubmittedEvent
    {
        $this->feedbackFormResponse = $feedbackFormResponse;
        return $this;
    }
}
