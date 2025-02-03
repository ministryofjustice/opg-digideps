<?php

declare(strict_types=1);

namespace Tests\App\Event;

use App\Event\ClientDeletedEvent;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class ClientDeletedEventTest extends TestCase
{
    /** @test */
    public function eventIsInitialisedCorrectly()
    {
        $client = ClientHelpers::createClient();
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientDeletedEvent($client, $currentUser, $trigger);

        self::assertEquals($client->getCaseNumber(), $event->getClientWithUsers()->getCaseNumber());
        self::assertEquals($client->getCourtDate(), $event->getClientWithUsers()->getCourtDate());
        self::assertEquals($currentUser->getEmail(), $event->getCurrentUser()->getEmail());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
