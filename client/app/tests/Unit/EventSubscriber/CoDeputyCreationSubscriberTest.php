<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\CoDeputyCreatedEvent;
use OPG\Digideps\Frontend\Event\CoDeputyInvitedEvent;
use OPG\Digideps\Frontend\EventSubscriber\CoDeputyCreationSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CoDeputyCreationSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [CoDeputyInvitedEvent::NAME => 'sendEmail', CoDeputyCreatedEvent::NAME => 'sendEmail'],
            CoDeputyCreationSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();
        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendInvitationEmail($invitedCoDeputy, $inviterDeputy->getFullName())->shouldBeCalled();

        $sut = new CoDeputyCreationSubscriber($mailer->reveal());
        $sut->sendEmail($coDeputyInvitedEvent);
    }
}
