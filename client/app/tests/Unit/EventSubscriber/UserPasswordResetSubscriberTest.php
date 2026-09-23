<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserPasswordResetEvent;
use OPG\Digideps\Frontend\EventSubscriber\UserPasswordResetSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserPasswordResetSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [UserPasswordResetEvent::NAME => 'sendEmail'],
            UserPasswordResetSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $passwordResetUser = UserHelpers::createUser();
        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);

        $mailer = self::createMock(Mailer::class);
        $mailer->expects(self::once())->method('sendResetPasswordEmail')->with($passwordResetUser);

        new UserPasswordResetSubscriber($mailer)->sendEmail($passwordResetEvent);
    }
}
