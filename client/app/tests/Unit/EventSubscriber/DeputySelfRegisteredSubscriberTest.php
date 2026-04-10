<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputySelfRegisteredEvent;
use OPG\Digideps\Frontend\EventSubscriber\DeputySelfRegisteredSubscriber;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DeputySelfRegisteredSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [DeputySelfRegisteredEvent::NAME => 'sendEmail'],
            DeputySelfRegisteredSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $selfRegisteredDeputy = UserHelpers::createUser();
        $deputyRegisteredEvent = new DeputySelfRegisteredEvent($selfRegisteredDeputy);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($selfRegisteredDeputy)->shouldBeCalled();

        $sut = new DeputySelfRegisteredSubscriber($mailer->reveal());

        $sut->sendEmail($deputyRegisteredEvent);
    }
}
