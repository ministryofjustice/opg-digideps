<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserActivatedEvent;
use AppBundle\EventSubscriber\UserActivatedSubscriber;
use AppBundle\Service\Mailer\Mailer;
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
        $activatedUser = UserHelpers::createUser();
        $userActivatedEvent = new UserActivatedEvent($activatedUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($activatedUser)->shouldBeCalled();

        $sut = new UserActivatedSubscriber($mailer->reveal());
        $sut->sendEmail($userActivatedEvent);
    }
}
