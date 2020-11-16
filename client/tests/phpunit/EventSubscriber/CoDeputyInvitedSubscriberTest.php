<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\EventSubscriber\CoDeputyInvitedSubscriber;
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
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $inviteCoDeputyEmail = EmailHelpers::createEmail();
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();

        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);

        $mailFactory
            ->createInvitationEmail($invitedCoDeputy, $inviterDeputy->getFullName())
            ->shouldBeCalled()
            ->willReturn($inviteCoDeputyEmail);

        $mailSender->send($inviteCoDeputyEmail)->shouldBeCalled();

        $sut = (new CoDeputyInvitedSubscriber())
            ->setMailFactory($mailFactory->reveal())
            ->setMailSender($mailSender->reveal());

        $sut->sendEmail($coDeputyInvitedEvent);
    }
}
