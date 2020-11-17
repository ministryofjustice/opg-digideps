<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\DeputyInvitedEvent;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyInvitedSubscriber extends Mailer implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            DeputyInvitedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(DeputyInvitedEvent $event)
    {
        $invitationEmail = $this->mailFactory->createInvitationEmail($event->getInvitedDeputy());
        $this->mailSender->send($invitationEmail);
    }
}
