<?php declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Event\DeputyInvitedEvent;
use AppBundle\EventSubscriber\CoDeputyInvitedSubscriber;
use AppBundle\EventSubscriber\DeputyInvitedSubscriber;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\EmailHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class DeputyInvitedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [DeputyInvitedEvent::NAME => 'sendEmail'],
            DeputyInvitedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $mailFactory = self::prophesize(MailFactory::class);
        $mailSender = self::prophesize(MailSender::class);

        $inviteDeputyEmail = EmailHelpers::createEmail();
        $invitedDeputy = UserHelpers::createUser();

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);

        $mailFactory
            ->createInvitationEmail($invitedDeputy)
            ->shouldBeCalled()
            ->willReturn($inviteDeputyEmail);

        $mailSender->send($inviteDeputyEmail)->shouldBeCalled();

        $sut = new DeputyInvitedSubscriber($mailFactory->reveal(), $mailSender->reveal());

        $sut->sendEmail($deputyInvitedEvent);
    }
}
