<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\DeputyInvitedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyInvitedSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            DeputyInvitedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(DeputyInvitedEvent $event)
    {
        $this->mailer->sendInvitationEmail($event->getInvitedDeputy());
    }
}
