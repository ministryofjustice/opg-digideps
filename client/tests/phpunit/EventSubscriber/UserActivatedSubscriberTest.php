<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserActivatedEvent;
use AppBundle\EventSubscriber\UserActivatedSubscriber;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\EmailHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserActivatedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [UserActivatedEvent::NAME => 'sendEmail'],
            UserActivatedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $userActivatedEmail = EmailHelpers::createEmail();
        $activatedUser = UserHelpers::createUser();

        $userActivatedEvent = new UserActivatedEvent($activatedUser);

        $mailFactory->createActivationEmail($activatedUser)->shouldBeCalled()->willReturn($userActivatedEmail);
        $mailSender->send($userActivatedEmail)->shouldBeCalled();

        $sut = new UserActivatedSubscriber($mailFactory->reveal(), $mailSender->reveal());

        $sut->sendEmail($userActivatedEvent);
    }
}
