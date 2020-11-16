<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserActivatedEvent;
use AppBundle\Service\Mailer\BaseMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserActivatedSubscriber extends BaseMailer implements EventSubscriberInterface
{
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
