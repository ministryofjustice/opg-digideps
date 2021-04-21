<?php declare(strict_types=1);


namespace App\Event;

use App\TestHelpers\UserHelper;
use PHPUnit\Framework\TestCase;

class UserDeletedEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
    {
        $deletedUser = UserHelper::createUser();
        $deletedBy = UserHelper::createUser();
        $trigger = 'A_TRIGGER';

        $event = new UserDeletedEvent($deletedUser, $deletedBy, $trigger);

        self::assertEquals($deletedUser, $event->getDeletedUser());
        self::assertEquals($deletedBy, $event->getDeletedBy());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
