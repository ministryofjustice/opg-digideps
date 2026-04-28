<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserPasswordResetEvent;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserPasswordResetSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserPasswordResetEvent::NAME => 'sendEmail',
        ];
    }

    public function sendEmail(UserPasswordResetEvent $event)
    {
        $this->mailer->sendResetPasswordEmail($event->getPasswordResetUser());
    }
}
