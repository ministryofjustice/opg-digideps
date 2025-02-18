<?php

namespace App\Tests\Integration\Event;

use App\Entity\User;
use App\Event\UserRetentionPolicyCommandEvent;
use App\TestHelpers\UserTestHelper;
use PHPUnit\Framework\TestCase;

class UserRetentionPolicyCommandEventTest extends TestCase
{
    /**
     * @test
     */
    public function eventIsInitialisedCorrectly()
    {
        $deletedAdminUser = new UserTestHelper();
        $user = $deletedAdminUser->createUser(null, User::ROLE_ADMIN_MANAGER)
            ->setLastLoggedIn(new \DateTime('-36 months'));
        $trigger = 'A_TRIGGER';

        $event = new UserRetentionPolicyCommandEvent($user, $trigger);

        self::assertEquals($user, $event->getDeletedAdminUser());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
