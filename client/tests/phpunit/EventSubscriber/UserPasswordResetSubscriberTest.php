<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserPasswordResetEvent;
use AppBundle\EventSubscriber\UserPasswordResetSubscriber;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\EmailHelpers;
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
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $passwordResetEmail = EmailHelpers::createEmail();
        $passwordResetUser = UserHelpers::createUser();

        $passwordResetEvent = new UserPasswordResetEvent($passwordResetUser);

        $mailFactory->createResetPasswordEmail($passwordResetUser)->shouldBeCalled()->willReturn($passwordResetEmail);
        $mailSender->send($passwordResetEmail)->shouldBeCalled();

        $sut = new UserPasswordResetSubscriber($mailFactory->reveal(), $mailSender->reveal());

        $sut->sendEmail($passwordResetEvent);
    }
}
