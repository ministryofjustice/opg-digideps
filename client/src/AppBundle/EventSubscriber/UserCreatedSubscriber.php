<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserCreatedEvent;
use AppBundle\Service\Mailer\BaseMailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreatedSubscriber extends BaseMailer implements EventSubscriberInterface
{
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
