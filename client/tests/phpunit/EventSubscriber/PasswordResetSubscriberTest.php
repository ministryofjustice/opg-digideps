<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\PasswordResetEvent;
use AppBundle\EventSubscriber\PasswordResetSubscriber;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\EmailHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class PasswordResetSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [PasswordResetEvent::NAME => 'sendEmail'],
            PasswordResetSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $passwordResetEmail = EmailHelpers::createEmail();
        $passwordResetUser = UserHelpers::createUser();

        $passwordResetEvent = new PasswordResetEvent($passwordResetUser);

        $mailFactory->createActivationEmail($passwordResetUser)->shouldBeCalled()->willReturn($passwordResetEmail);
        $mailSender->send($passwordResetEmail)->shouldBeCalled();


        $sut = new PasswordResetSubscriber($mailFactory->reveal(), $mailSender->reveal());

        $sut->sendEmail($passwordResetEvent);
    }
}
