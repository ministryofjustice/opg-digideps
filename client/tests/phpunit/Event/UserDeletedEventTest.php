<?php declare(strict_types=1);


namespace App\Event;

use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserDeletedEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
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
