<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserActivatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\UserActivatedSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserActivatedSubscriberTest extends TestCase
{
    use ProphecyTrait;

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
