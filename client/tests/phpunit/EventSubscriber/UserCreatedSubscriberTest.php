<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserCreatedEvent;
use AppBundle\EventSubscriber\UserCreatedSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class UserCreatedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [UserCreatedEvent::NAME => 'sendEmail'],
            UserCreatedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $createdUser = UserHelpers::createUser();
        $userCreatedEvent = new UserCreatedEvent($createdUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($createdUser)->shouldBeCalled();

        $sut = new UserCreatedSubscriber($mailer->reveal());
        $sut->sendEmail($userCreatedEvent);
    }
}
