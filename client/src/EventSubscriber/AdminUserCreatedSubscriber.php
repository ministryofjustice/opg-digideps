<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\AdminUserCreatedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminUserCreatedSubscriber implements EventSubscriberInterface
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
            AdminUserCreatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(AdminUserCreatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getCreatedUser());
    }
}
