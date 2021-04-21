<?php declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\UserPasswordResetEvent;
use App\EventSubscriber\UserPasswordResetSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\UserHelper;
use PHPUnit\Framework\TestCase;

class UserPasswordResetSubscriberTest extends TestCase
{
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
        $passwordResetUser = UserHelper::createUser();
        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendResetPasswordEmail($passwordResetUser)->shouldBeCalled();

        $sut = new UserPasswordResetSubscriber($mailer->reveal());
        $sut->sendEmail($passwordResetEvent);
    }
}
