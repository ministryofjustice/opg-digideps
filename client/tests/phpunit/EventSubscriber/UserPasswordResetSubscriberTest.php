<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserPasswordResetEvent;
use AppBundle\EventSubscriber\UserPasswordResetSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\UserHelpers;
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
        $passwordResetUser = UserHelpers::createUser();
        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendResetPasswordEmail($passwordResetUser)->shouldBeCalled();

        $sut = new UserPasswordResetSubscriber($mailer->reveal());
        $sut->sendEmail($passwordResetEvent);
    }
}
