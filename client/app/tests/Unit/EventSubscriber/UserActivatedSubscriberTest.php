<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserActivatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\UserActivatedSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserActivatedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [UserActivatedEvent::NAME => 'sendEmail'],
            UserActivatedSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $activatedUser = UserHelpers::createUser();
        $userActivatedEvent = new UserActivatedEvent($activatedUser);

        $mailer = self::createMock(Mailer::class);
        $mailer->expects(self::once())->method('sendActivationEmail')->with($activatedUser);

        new UserActivatedSubscriber($mailer)->sendEmail($userActivatedEvent);
    }
}
