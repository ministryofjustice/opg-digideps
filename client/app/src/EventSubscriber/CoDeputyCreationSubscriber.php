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
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
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
