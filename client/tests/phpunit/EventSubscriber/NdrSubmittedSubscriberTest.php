<?php declare(strict_types=1);


namespace Tests\App\EventListener;

use App\Event\NdrSubmittedEvent;
use App\EventSubscriber\NdrSubmittedSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\NdrHelper;
use App\TestHelpers\ReportHelper;
use App\TestHelpers\UserHelper;
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
        $submittedBy = UserHelper::createUser();
        $submittedNdr = NdrHelper::createNdr();
        $newReport = ReportHelper::createReport();

        $mailer = self::prophesize(Mailer::class);
        $mailer
            ->sendNdrSubmissionConfirmationEmail($submittedBy, $submittedNdr, $newReport)
            ->shouldBeCalled();

        $event = new NdrSubmittedEvent($submittedBy, $submittedNdr, $newReport);

        $sut = new NdrSubmittedSubscriber($mailer->reveal());
        $sut->sendEmail($event);
    }
}
