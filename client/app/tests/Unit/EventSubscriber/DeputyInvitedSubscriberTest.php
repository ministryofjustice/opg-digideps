<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputyInvitedEvent;
use OPG\Digideps\Frontend\EventSubscriber\DeputyInvitedSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DeputyInvitedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [DeputyInvitedEvent::NAME => 'sendEmail'],
            DeputyInvitedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $invitedDeputy = UserHelpers::createUser();
        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendInvitationEmail($invitedDeputy)->shouldBeCalled();

        $sut = new DeputyInvitedSubscriber($mailer->reveal());

        $sut->sendEmail($deputyInvitedEvent);
    }
}
