<?php declare(strict_types=1);

namespace Tests\App\Event;

use App\Event\ClientUpdatedEvent;
use App\TestHelpers\ClientHelper;
use App\TestHelpers\UserHelper;
use PHPUnit\Framework\TestCase;

class ClientUpdatedEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
    {
        $preUpdateClient = ClientHelper::createClient();
        $postUpdateClient = ClientHelper::createClient();
        $changedBy = UserHelper::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $changedBy, $trigger);

        self::assertEquals($preUpdateClient, $event->getPreUpdateClient());
        self::assertEquals($postUpdateClient, $event->getPostUpdateClient());
        self::assertEquals($changedBy, $event->getChangedBy());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
