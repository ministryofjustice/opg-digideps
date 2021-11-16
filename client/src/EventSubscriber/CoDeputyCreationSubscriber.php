<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\CoDeputyCreatedEvent;
use App\Event\CoDeputyCreationEventInterface;
use App\Event\CoDeputyInvitedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoDeputyCreationSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            CoDeputyInvitedEvent::NAME => 'sendEmail',
            CoDeputyCreatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(CoDeputyCreationEventInterface $event)
    {
        $this->mailer->sendInvitationEmail($event->getInvitedCoDeputy(), $event->getInviterDeputy()->getFullName());
    }
}
