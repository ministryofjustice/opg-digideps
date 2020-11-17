<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserCreatedEvent;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreatedSubscriber implements EventSubscriberInterface
{
    /** @var Mailer */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserCreatedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(UserCreatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getCreatedUser());
    }
}
