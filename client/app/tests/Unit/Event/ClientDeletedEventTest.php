<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Event;

use OPG\Digideps\Frontend\Event\ClientDeletedEvent;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
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
