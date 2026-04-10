<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserPasswordResetEvent;
use OPG\Digideps\Frontend\EventSubscriber\UserPasswordResetSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserPasswordResetSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [UserPasswordResetEvent::NAME => 'sendEmail'],
            UserPasswordResetSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $passwordResetUser = UserHelpers::createUser();
        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendResetPasswordEmail($passwordResetUser)->shouldBeCalled();

        $sut = new UserPasswordResetSubscriber($mailer->reveal());
        $sut->sendEmail($passwordResetEvent);
    }
}
