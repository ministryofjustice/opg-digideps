<?php

declare(strict_types=1);

namespace App\Event;

use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserUpdatedEventTest extends TestCase
{
    /** @test */
    public function eventIsInitialisedCorrectly()
    {
        $preUpdateUser = UserHelpers::createUser();
        $postUpdateUser = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);

        self::assertEquals($currentUser->getEmail(), $event->getCurrentUser()->getEmail());
        self::assertEquals($postUpdateUser->getEmail(), $event->getPostUpdateUser()->getEmail());
        self::assertEquals($postUpdateUser->getFullName(), $event->getPostUpdateUser()->getFullName());
        self::assertEquals($postUpdateUser->getRoleName(), $event->getPostUpdateUser()->getRoleName());
        self::assertEquals($preUpdateUser->getEmail(), $event->getPreUpdateUser()->getEmail());
        self::assertEquals($preUpdateUser->getRoleName(), $event->getPreUpdateUser()->getRoleName());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
