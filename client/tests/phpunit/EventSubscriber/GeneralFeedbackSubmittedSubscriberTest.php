<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\EventSubscriber\GeneralFeedbackSubmittedSubscriber;
use AppBundle\Service\Mailer\Mailer;
use PHPUnit\Framework\TestCase;

class GeneralFeedbackSubmittedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [GeneralFeedbackSubmittedEvent::NAME => 'sendEmail'],
            GeneralFeedbackSubmittedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $feedbackFormResponse = ['some response' => 'some answer'];
        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($feedbackFormResponse);

        $mailer = self::prophesize(Mailer::class);
        $mailer->sendGeneralFeedbackEmail($feedbackFormResponse)->shouldBeCalled();

        $sut = new GeneralFeedbackSubmittedSubscriber($mailer->reveal());
        $sut->sendEmail($event);
    }
}
