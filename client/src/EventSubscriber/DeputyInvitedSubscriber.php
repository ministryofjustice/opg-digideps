<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\DeputyInvitedEvent;
use App\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyInvitedSubscriber implements EventSubscriberInterface
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
            DeputyInvitedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(DeputyInvitedEvent $event)
    {
        $this->mailer->sendInvitationEmail($event->getInvitedDeputy());
    }
}
