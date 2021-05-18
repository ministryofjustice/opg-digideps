<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class GeneralFeedbackSubmittedEvent extends Event
{
    public const NAME = 'general.feedback.submitted';

    /** @var array */
    private $feedbackFormResponse;

    public function getFeedbackFormResponse(): array
    {
        return $this->feedbackFormResponse;
    }

    public function setFeedbackFormResponse(array $feedbackFormResponse): GeneralFeedbackSubmittedEvent
    {
        $this->feedbackFormResponse = $feedbackFormResponse;

        return $this;
    }
}
