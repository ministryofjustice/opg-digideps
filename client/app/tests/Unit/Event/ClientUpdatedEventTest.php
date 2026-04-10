<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Event;

use OPG\Digideps\Frontend\Event\ClientUpdatedEvent;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class ClientUpdatedEventTest extends TestCase
{
    /** @test */
    public function eventIsInitialisedCorrectly()
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
