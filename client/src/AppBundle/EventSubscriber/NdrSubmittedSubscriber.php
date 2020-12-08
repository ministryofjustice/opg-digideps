<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\NdrSubmittedEvent;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NdrSubmittedSubscriber implements EventSubscriberInterface
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
            NdrSubmittedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(NdrSubmittedEvent $event)
    {
        $this->mailer->sendNdrSubmissionConfirmationEmail(
            $event->getSubmittedBy(),
            $event->getSubmittedNdr(),
            $event->getNewReport()
        );
    }
}
