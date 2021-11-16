<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\AdminUserCreatedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminUserCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(private Mailer $mailer)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            AdminUserCreatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(AdminUserCreatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getCreatedUser());
    }
}
