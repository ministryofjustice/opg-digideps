<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\Event\PostSubmissionFeedbackSubmittedEvent;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FeedbackSubmittedSubscriber implements EventSubscriberInterface
{
    /** @var Mailer */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            GeneralFeedbackSubmittedEvent::NAME => 'sendGeneralFeedbackEmail',
            PostSubmissionFeedbackSubmittedEvent::NAME => 'sendPostSubmissionFeedbackEmail'
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
