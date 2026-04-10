<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Event;

use OPG\Digideps\Frontend\Event\UserDeletedEvent;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserDeletedEventTest extends TestCase
{
    /** @test */
    public function eventIsInitialisedCorrectly()
    {
        $deletedUser = UserHelpers::createUser();
        $deletedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new UserDeletedEvent($deletedUser, $deletedBy, $trigger);

        self::assertEquals($deletedUser, $event->getDeletedUser());
        self::assertEquals($deletedBy, $event->getDeletedBy());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
