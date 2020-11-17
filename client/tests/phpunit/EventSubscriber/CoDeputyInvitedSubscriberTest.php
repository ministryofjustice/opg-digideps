<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\EventSubscriber\CoDeputyInvitedSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\EmailHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class CoDeputyInvitedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [CoDeputyInvitedEvent::NAME => 'sendEmail'],
            CoDeputyInvitedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();
        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendInvitationEmail($invitedCoDeputy, $inviterDeputy->getFullName())->shouldBeCalled();

        $sut = new CoDeputyInvitedSubscriber($mailer->reveal());
        $sut->sendEmail($coDeputyInvitedEvent);
    }
}
