<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserCreatedEvent;
use AppBundle\EventSubscriber\UserCreatedSubscriber;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\EmailHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserCreatedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [UserCreatedEvent::NAME => 'sendEmail'],
            UserCreatedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $createdUser = UserHelpers::createUser();
        $userCreatedEmail = EmailHelpers::createEmail();
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $userCreatedEvent = new UserCreatedEvent($createdUser);

        $mailFactory->createActivationEmail($createdUser)->shouldBeCalled()->willReturn($userCreatedEmail);
        $mailSender->send($userCreatedEmail)->shouldBeCalled();

        $sut = (new UserCreatedSubscriber())
            ->setMailFactory($mailFactory->reveal())
            ->setMailSender($mailSender->reveal());

        $sut->sendEmail($userCreatedEvent);
    }
}
