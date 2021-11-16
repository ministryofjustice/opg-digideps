<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\OrgUserCreatedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrgUserCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            OrgUserCreatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(OrgUserCreatedEvent $event)
    {
        $this->mailer->sendInvitationEmail($event->getCreatedUser());
    }
}
