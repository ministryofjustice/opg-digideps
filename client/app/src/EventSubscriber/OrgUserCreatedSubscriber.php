<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\OrgUserCreatedEvent;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrgUserCreatedSubscriber implements EventSubscriberInterface
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
            OrgUserCreatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(OrgUserCreatedEvent $event)
    {
        $this->mailer->sendInvitationEmail($event->getCreatedUser());
    }
}
