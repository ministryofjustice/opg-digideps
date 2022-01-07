<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\NdrSubmittedEvent;
use App\EventSubscriber\NdrSubmittedSubscriber;
use App\Service\Mailer\Mailer;
use App\TestHelpers\NdrHelpers;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class NdrSubmittedSubscriberTest extends TestCase
{
    use ProphecyTrait;

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
