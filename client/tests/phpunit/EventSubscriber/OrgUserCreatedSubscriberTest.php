<?php declare(strict_types=1);


namespace Tests\App\EventListener;

use App\Event\OrgUserCreatedEvent;
use App\EventSubscriber\OrgUserCreatedSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\UserHelper;
use PHPUnit\Framework\TestCase;

class OrgUserCreatedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            ['org.user.created' => 'sendEmail'],
            OrgUserCreatedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $createdUser = UserHelper::createUser();
        $userCreatedEvent = new OrgUserCreatedEvent($createdUser);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendInvitationEmail($createdUser)->shouldBeCalled();

        $sut = new OrgUserCreatedSubscriber($mailer->reveal());
        $sut->sendEmail($userCreatedEvent);
    }
}
