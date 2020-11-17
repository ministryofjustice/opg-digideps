<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoDeputyInvitedSubscriber implements EventSubscriberInterface
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
            CoDeputyInvitedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(CoDeputyInvitedEvent $event)
    {
        $this->mailer->sendInvitationEmail($event->getInvitedCoDeputy(), $event->getInviterDeputy()->getFullName());
    }
}
