<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\PasswordResetEvent;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PasswordResetSubscriber implements EventSubscriberInterface
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
            PasswordResetEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(PasswordResetEvent $event)
    {
        $passwordResetEmail = $this->mailFactory->createActivationEmail($event->getPasswordResetUser());
        $this->mailSender->send($passwordResetEmail);
    }
}
