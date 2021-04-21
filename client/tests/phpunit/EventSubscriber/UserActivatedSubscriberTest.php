<?php declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\UserActivatedEvent;
use App\EventSubscriber\UserActivatedSubscriber;
use App\Service\Mailer\Mailer;
use App\Service\Mailer\MailFactory;
use App\Service\Mailer\MailSender;
use App\TestHelpers\EmailHelper;
use App\TestHelpers\UserHelper;
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
        $activatedUser = UserHelper::createUser();
        $userActivatedEvent = new UserActivatedEvent($activatedUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($activatedUser)->shouldBeCalled();

        $sut = new UserActivatedSubscriber($mailer->reveal());
        $sut->sendEmail($userActivatedEvent);
    }
}
