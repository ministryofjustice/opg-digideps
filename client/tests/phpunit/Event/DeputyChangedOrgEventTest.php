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
        $previousDeputyOrg = ClientHelpers::createClient();
        $client = ClientHelpers::createClient();
        $trigger = 'A_TRIGGER';

        var_dump($previousDeputyOrg);

        $event = new DeputyChangedOrgEvent($trigger, $previousDeputyOrg, $client);

            self::assertEquals($trigger, $event->getTrigger());
            self::assertEquals($previousDeputyOrg->getOrganisation(), $event->getPreviousDeputyOrg());
            self::assertEquals($client, $event->getClient());

        var_dump('-----');
        var_dump($event->getPreviousDeputyOrg());
    }
}

