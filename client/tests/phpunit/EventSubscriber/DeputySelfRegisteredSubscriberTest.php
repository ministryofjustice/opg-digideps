<?php declare(strict_types=1);


namespace Tests\App\EventListener;

use App\Event\DeputySelfRegisteredEvent;
use App\EventSubscriber\DeputySelfRegisteredSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class DeputySelfRegisteredSubscriberTest extends TestCase
{
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
