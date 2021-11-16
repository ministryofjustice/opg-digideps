<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\NdrSubmittedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NdrSubmittedSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            NdrSubmittedEvent::NAME => 'sendEmail',
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
