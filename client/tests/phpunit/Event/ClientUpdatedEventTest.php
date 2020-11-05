<?php declare(strict_types=1);

namespace Tests\AppBundle\Event;

use AppBundle\Event\ClientUpdatedEvent;
use AppBundle\TestHelpers\ClientHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class ClientUpdatedEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = ClientHelpers::createClient();
        $changedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        self::assertEquals($preUpdateClient, $event->getPreUpdateClient());
        self::assertEquals($postUpdateClient, $event->getPostUpdateClient());
        self::assertEquals($changedBy, $event->getChangedBy());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
