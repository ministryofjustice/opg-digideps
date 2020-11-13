<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserCreatedEvent;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreatedSubscriber implements EventSubscriberInterface
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
            UserCreatedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(UserCreatedEvent $event)
    {
        $activationEmail = $this->mailFactory->createActivationEmail($event->getCreatedUser());
        $this->mailSender->send($activationEmail);
    }
}
