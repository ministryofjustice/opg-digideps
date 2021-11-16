<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use App\Model\FeedbackReport;
use Symfony\Contracts\EventDispatcher\Event;

class PostSubmissionFeedbackSubmittedEvent extends Event
{
    public const NAME = 'post.submission.feedback.submitted';

    /** @var FeedbackReport */
    private $formResponse;

    /** @var User */
    private $submittedByUser;

    public function __construct(FeedbackReport $formResponse, User $submittedByUser)
    {
        $this->formResponse = $formResponse;
        $this->submittedByUser = $submittedByUser;
    }

    public function getFormResponse(): FeedbackReport
    {
        return $this->formResponse;
    }

    public function setFormResponse(FeedbackReport $formResponse): PostSubmissionFeedbackSubmittedEvent
    {
        $this->formResponse = $formResponse;

        return $this;
    }

    public function getSubmittedByUser(): User
    {
        return $this->submittedByUser;
    }

    public function setSubmittedByUser(User $submittedByUser): PostSubmissionFeedbackSubmittedEvent
    {
        $this->submittedByUser = $submittedByUser;

        return $this;
    }
}
