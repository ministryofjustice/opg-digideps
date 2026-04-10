<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputySelfRegisteredEvent;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputySelfRegisteredSubscriber implements EventSubscriberInterface
{
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            DeputySelfRegisteredEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(DeputySelfRegisteredEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getRegisteredDeputy());
    }
}
