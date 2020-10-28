<?php declare(strict_types=1);


namespace AppBundle\EventListener;

use AppBundle\Event\UserUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUpdatedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            UserUpdatedEvent::NAME => 'auditLog',
        ];
    }

    public function auditLog()
    {
    }
}
