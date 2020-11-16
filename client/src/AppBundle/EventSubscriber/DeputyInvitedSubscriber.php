<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\DeputyInvitedEvent;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyInvitedSubscriber implements EventSubscriberInterface
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
            DeputyInvitedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(DeputyInvitedEvent $event)
    {
        $invitationEmail = $this->mailFactory->createInvitationEmail($event->getInvitedDeputy());
        $this->mailSender->send($invitationEmail);
    }
}
