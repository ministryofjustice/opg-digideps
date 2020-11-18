<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\CoDeputyCreatedEvent;
use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Event\CoDeputyCreationEventInterface;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoDeputyCreationSubscriber implements EventSubscriberInterface
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
            CoDeputyInvitedEvent::NAME => 'sendEmail',
            CoDeputyCreatedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(CoDeputyCreationEventInterface $event)
    {
        $this->mailer->sendInvitationEmail($event->getInvitedCoDeputy(), $event->getInviterDeputy()->getFullName());
    }
}
