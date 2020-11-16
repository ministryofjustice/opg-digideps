<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserActivatedEvent;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserActivatedSubscriber implements EventSubscriberInterface
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
            UserActivatedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(UserActivatedEvent $event)
    {
        $userActivatedEmail = $this->mailFactory->createActivationEmail($event->getActivatedUser());
        $this->mailSender->send($userActivatedEmail);
    }
}
