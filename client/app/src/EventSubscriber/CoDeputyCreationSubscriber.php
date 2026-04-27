<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\CoDeputyCreatedEvent;
use OPG\Digideps\Frontend\Event\CoDeputyCreationEventInterface;
use OPG\Digideps\Frontend\Event\CoDeputyInvitedEvent;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
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
