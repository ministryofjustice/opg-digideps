<?php declare(strict_types=1);

namespace Tests\App\Event;

use App\Event\ClientDeletedEvent;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class ClientDeletedEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
    {
        $client = ClientHelpers::createClient();
        $currentUser = UserHelpers::createUser();
        $deletedDeputy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new ClientDeletedEvent($client, $currentUser, $deletedDeputy, $trigger);

        self::assertEquals($client->getCaseNumber(), $event->getCaseNumber());
        self::assertEquals($client->getCourtDate(), $event->getDeputyshipStartDate());
        self::assertEquals($currentUser->getEmail(), $event->getDischargedByEmail());
        self::assertEquals($deletedDeputy->getFullName(), $event->getDischargedDeputyName());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
