<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UserActivatedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserActivatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            UserActivatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(UserActivatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getActivatedUser());
    }
}
