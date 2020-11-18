<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\CoDeputyCreatedEvent;
use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\EventSubscriber\CoDeputyCreationSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class CoDeputyCreationSubscriberTest extends TestCase
{
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
