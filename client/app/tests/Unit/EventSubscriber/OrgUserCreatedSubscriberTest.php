<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\OrgUserCreatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\OrgUserCreatedSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class OrgUserCreatedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            ['org.user.created' => 'sendEmail'],
            OrgUserCreatedSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $createdUser = UserHelpers::createUser();
        $userCreatedEvent = new OrgUserCreatedEvent($createdUser);

        $mailer = self::createMock(Mailer::class);
        $mailer->expects(self::once())->method('sendInvitationEmail')->with($createdUser);

        new OrgUserCreatedSubscriber($mailer)->sendEmail($userCreatedEvent);
    }
}
