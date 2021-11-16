<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\GeneralFeedbackSubmittedEvent;
use App\Event\PostSubmissionFeedbackSubmittedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FeedbackSubmittedSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            GeneralFeedbackSubmittedEvent::NAME => 'sendGeneralFeedbackEmail',
            PostSubmissionFeedbackSubmittedEvent::NAME => 'sendPostSubmissionFeedbackEmail',
        ];
    }

    public function sendGeneralFeedbackEmail(GeneralFeedbackSubmittedEvent $event)
    {
        $this->mailer->sendGeneralFeedbackEmail($event->getFeedbackFormResponse());
    }

    public function sendPostSubmissionFeedbackEmail(PostSubmissionFeedbackSubmittedEvent $event)
    {
        $this->mailer->sendPostSubmissionFeedbackEmail($event->getFormResponse(), $event->getSubmittedByUser());
    }
}
