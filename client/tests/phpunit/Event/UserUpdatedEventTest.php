<?php declare(strict_types=1);

namespace App\Event;

use App\TestHelpers\UserHelper;
use PHPUnit\Framework\TestCase;

class UserUpdatedEventTest extends TestCase
{
    /** @test */
    public function event_is_initialised_correctly()
    {
        $preUpdateUser = UserHelper::createUser();
        $postUpdateUser = UserHelper::createUser();
        $currentUser = UserHelper::createUser();
        $trigger = 'A_TRIGGER';

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);

        self::assertEquals($currentUser->getEmail(), $event->getCurrentUserEmail());
        self::assertEquals($postUpdateUser->getEmail(), $event->getPostUpdateEmail());
        self::assertEquals($postUpdateUser->getFullName(), $event->getPostUpdateFullName());
        self::assertEquals($postUpdateUser->getRoleName(), $event->getPostUpdateRoleName());
        self::assertEquals($preUpdateUser->getEmail(), $event->getPreUpdateEmail());
        self::assertEquals($preUpdateUser->getRoleName(), $event->getPreUpdateRoleName());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
