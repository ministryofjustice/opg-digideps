<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputyInvitedEvent;
use OPG\Digideps\Frontend\EventSubscriber\DeputyInvitedSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class DeputyInvitedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [DeputyInvitedEvent::NAME => 'sendEmail'],
            DeputyInvitedSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $invitedDeputy = UserHelpers::createUser();
        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);

        $mailer = self::createMock(Mailer::class);
        $mailer->expects(self::once())
            ->method('sendInvitationEmail')
            ->with($invitedDeputy);

        new DeputyInvitedSubscriber($mailer)->sendEmail($deputyInvitedEvent);
    }
}
