<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\AdminUserCreatedEvent;
use AppBundle\EventSubscriber\AdminUserCreatedSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class AdminUserCreatedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [AdminUserCreatedEvent::NAME => 'sendEmail'],
            AdminUserCreatedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $createdUser = UserHelpers::createUser();
        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($createdUser)->shouldBeCalled();

        $sut = new AdminUserCreatedSubscriber($mailer->reveal());
        $sut->sendEmail($userCreatedEvent);
    }
}
