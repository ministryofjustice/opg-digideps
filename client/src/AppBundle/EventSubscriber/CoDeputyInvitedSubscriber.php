<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Service\Mailer\BaseMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoDeputyInvitedSubscriber extends BaseMailer implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CoDeputyInvitedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(CoDeputyInvitedEvent $event)
    {
        $invitationEmail = $this->mailFactory->createInvitationEmail(
            $event->getInvitedCoDeputy(),
            $event->getInviterDeputy()->getFullName()
        );

        $this->mailSender->send($invitationEmail);
    }
}
