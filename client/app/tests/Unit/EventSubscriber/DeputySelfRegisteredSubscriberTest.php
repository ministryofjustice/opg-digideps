<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputySelfRegisteredEvent;
use OPG\Digideps\Frontend\EventSubscriber\DeputySelfRegisteredSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class DeputySelfRegisteredSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [DeputySelfRegisteredEvent::NAME => 'sendEmail'],
            DeputySelfRegisteredSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $selfRegisteredDeputy = UserHelpers::createUser();
        $deputyRegisteredEvent = new DeputySelfRegisteredEvent($selfRegisteredDeputy);

        $mailer = self::createMock(Mailer::class);
        $mailer->expects(self::once())->method('sendActivationEmail')->with($selfRegisteredDeputy);

        new DeputySelfRegisteredSubscriber($mailer)->sendEmail($deputyRegisteredEvent);
    }
}
