<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\CoDeputyCreatedEvent;
use App\Event\CoDeputyInvitedEvent;
use App\EventSubscriber\CoDeputyCreationSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\UserHelpers;
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
