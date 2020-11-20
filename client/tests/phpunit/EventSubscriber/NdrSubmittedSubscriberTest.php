<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\NdrSubmittedEvent;
use AppBundle\EventSubscriber\NdrSubmittedSubscriber;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\NdrHelpers;
use AppBundle\TestHelpers\ReportHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPStan\Testing\TestCase;

class NdrSubmittedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [NdrSubmittedEvent::NAME => 'sendEmail'],
            NdrSubmittedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $submittedBy = UserHelpers::createUser();
        $submittedNdr = NdrHelpers::createNdr();
        $newReport = ReportHelpers::createReport();

        $mailer = self::prophesize(Mailer::class);
        $mailer
            ->sendNdrSubmissionConfirmationEmail($submittedBy, $submittedNdr, $newReport)
            ->shouldBeCalled();

        $event = new NdrSubmittedEvent($submittedBy, $submittedNdr, $newReport);

        $sut = new NdrSubmittedSubscriber($mailer->reveal());
        $sut->sendEmail($event);
    }
}
