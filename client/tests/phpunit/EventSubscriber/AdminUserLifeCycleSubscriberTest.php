<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\AdminUserCreatedEvent;
use App\EventSubscriber\AdminUserLifeCycleSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AdminUserLifeCycleSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [AdminUserCreatedEvent::NAME => 'sendEmail'],
            AdminUserLifeCycleSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $createdUser = UserHelpers::createUser();
        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendActivationEmail($createdUser)->shouldBeCalled();

        $sut = new AdminUserLifeCycleSubscriber($mailer->reveal());
        $sut->sendEmail($userCreatedEvent);
    }
}
