<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\CoDeputyCreatedEvent;
use OPG\Digideps\Frontend\Event\CoDeputyInvitedEvent;
use OPG\Digideps\Frontend\EventSubscriber\CoDeputyCreationSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class CoDeputyCreationSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [CoDeputyInvitedEvent::NAME => 'sendEmail', CoDeputyCreatedEvent::NAME => 'sendEmail'],
            CoDeputyCreationSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();
        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);

        $mailer = self::createMock(Mailer::class);
        $mailer->expects(self::once())
            ->method('sendInvitationEmail')
            ->with($invitedCoDeputy, $inviterDeputy->getFullName());

        new CoDeputyCreationSubscriber($mailer)->sendEmail($coDeputyInvitedEvent);
    }
}
