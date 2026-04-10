<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserActivatedEvent;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserActivatedSubscriber implements EventSubscriberInterface
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
            UserActivatedEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(UserActivatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getActivatedUser());
    }
}
