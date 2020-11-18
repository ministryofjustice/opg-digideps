<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GeneralFeedbackSubmittedSubscriber implements EventSubscriberInterface
{
    /** @var Mailer */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return  [
            GeneralFeedbackSubmittedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(GeneralFeedbackSubmittedEvent $event)
    {
        $this->mailer->sendGeneralFeedbackEmail($event->getFeedbackFormResponse());
    }
}
