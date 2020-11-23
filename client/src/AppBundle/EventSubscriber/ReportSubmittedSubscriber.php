<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\ReportSubmittedEvent;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubmittedSubscriber implements EventSubscriberInterface
{
    /** @var ReportApi */
    private $reportApi;

    /* @var Mailer */
    private $mailer;

    public function __construct(ReportApi $reportApi, Mailer $mailer)
    {
        $this->reportApi = $reportApi;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            ReportSubmittedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(ReportSubmittedEvent $event)
    {
        if ($event->getNewYearReportId()) {
            $newReport = $this->reportApi->getReport(intval($event->getNewYearReportId()), ['submit']);
            $this->mailer->sendReportSubmissionConfirmationEmail($event->getSubmittedBy(), $event->getSubmittedReport(), $newReport);
        }
    }
}
