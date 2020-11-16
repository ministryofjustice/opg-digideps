<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserPasswordResetEvent;
use AppBundle\Service\Mailer\BaseMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserPasswordResetSubscriber extends BaseMailer implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            UserPasswordResetEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(UserPasswordResetEvent $event)
    {
        $passwordResetEmail = $this->mailFactory->createResetPasswordEmail($event->getPasswordResetUser());
        $this->mailSender->send($passwordResetEmail);
    }
}
