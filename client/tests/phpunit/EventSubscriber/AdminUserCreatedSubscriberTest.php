<?php declare(strict_types=1);


namespace Tests\App\EventListener;

use App\Event\AdminUserCreatedEvent;
use App\EventSubscriber\AdminUserCreatedSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\UserHelper;
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
        $createdUser = UserHelper::createUser();
        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($createdUser)->shouldBeCalled();

        $sut = new AdminUserCreatedSubscriber($mailer->reveal());
        $sut->sendEmail($userCreatedEvent);
    }
}
