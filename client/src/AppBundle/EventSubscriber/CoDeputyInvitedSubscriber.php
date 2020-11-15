<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoDeputyInvitedSubscriber implements EventSubscriberInterface
{
    /** @var MailFactory */
    private $mailFactory;

    /** @var MailSender */
    private $mailSender;

    public function __construct(MailFactory $mailFactory, MailSender $mailSender)
    {
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
    }

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
