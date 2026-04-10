<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Event;

use PHPUnit\Framework\Attributes\Test;
use DateTime;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Event\UserRetentionPolicyCommandEvent;
use OPG\Digideps\Backend\TestHelpers\UserTestHelper;
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
