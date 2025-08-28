<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\Test;
use DateTime;
use App\Entity\User;
use App\Event\UserRetentionPolicyCommandEvent;
use App\TestHelpers\UserTestHelper;
use PHPUnit\Framework\TestCase;

final class UserRetentionPolicyCommandEventTest extends TestCase
{
    #[Test]
    public function eventIsInitialisedCorrectly(): void
    {
        $deletedAdminUser = UserTestHelper::create();
        $user = $deletedAdminUser->createUser(null, User::ROLE_ADMIN_MANAGER)
            ->setLastLoggedIn(new DateTime('-36 months'));
        $trigger = 'A_TRIGGER';

        $event = new UserRetentionPolicyCommandEvent($user, $trigger);

        self::assertEquals($user, $event->getDeletedAdminUser());
        self::assertEquals($trigger, $event->getTrigger());
    }
}
