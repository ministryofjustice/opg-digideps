<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\TestHelpers\ClientHelpers;
use App\Event\DeputyChangedOrgEvent;

class DeputyChangedOrgEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
    {
        $trigger = 'A_TRIGGER';
        $client = ClientHelpers::createClient();
        $previousOrg = $client->getOrganisation()->getId();
        $newOrg =  $client->getOrganisation()->getId();

        $event = new DeputyChangedOrgEvent($trigger, $previousOrg, $newOrg, $client);

            self::assertEquals($trigger, $event->getTrigger());
            self::assertEquals($previousOrg, $event->getPreviousOrg());
            self::assertEquals($newOrg, $event->getNewOrg());
            self::assertEquals($client, $event->getClient());
    }
}

