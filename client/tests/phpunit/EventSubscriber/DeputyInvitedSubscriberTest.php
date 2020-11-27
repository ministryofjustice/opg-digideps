<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\DeputyInvitedEvent;
use AppBundle\EventSubscriber\DeputyInvitedSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class DeputyInvitedSubscriberTest extends TestCase
{
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
