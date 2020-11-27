<?php declare(strict_types=1);


namespace AppBundle\Service\Mailer;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Model\FeedbackReport;

class Mailer
{
    /** @var MailFactory */
    private $mailFactory;

    /** @var MailSender */
    private $mailSender;

    public function __construct(MailFactory $mailFactory, MailSender $mailSender)
    {
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
    }

    public function sendActivationEmail(User $activatedUser)
    {
        $this->mailSender->send($this->mailFactory->createActivationEmail($activatedUser));
    }

    public function sendInvitationEmail(User $invitedUser, string $deputyName = null)
    {
        $this->mailSender->send($this->mailFactory->createInvitationEmail($invitedUser, $deputyName));
    }

    public function sendResetPasswordEmail(User $passwordResetUser)
    {
        $this->mailSender->send($this->mailFactory->createResetPasswordEmail($passwordResetUser));
    }

    public function sendGeneralFeedbackEmail(array $feedbackFormResponse)
    {
        $this->mailSender->send($this->mailFactory->createGeneralFeedbackEmail($feedbackFormResponse));
    }

    public function sendPostSubmissionFeedbackEmail(FeedbackReport $submittedFeedbackReport, User $submittedByDeputy)
    {
        $this->mailSender->send(
            $this->mailFactory->createPostSubmissionFeedbackEmail($submittedFeedbackReport, $submittedByDeputy)
        );
    }

    public function sendUpdateClientDetailsEmail(Client $updatedClient)
    {
        $this->mailSender->send($this->mailFactory->createUpdateClientDetailsEmail($updatedClient));
    }

    public function sendUpdateDeputyDetailsEmail(User $updatedDeputy)
    {
        $this->mailSender->send($this->mailFactory->createUpdateDeputyDetailsEmail($updatedDeputy));
    }

    public function sendReportSubmissionConfirmationEmail(
        User $submittedByDeputy,
        ReportInterface $submittedReport,
        Report $newReport
    ) {
        $this->mailSender->send(
            $this->mailFactory->createReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport)
        );
    }

    public function sendNdrSubmissionConfirmationEmail(User $submittedByDeputy, Ndr $submittedNdr, Report $newReport)
    {
        $this->mailSender->send(
            $this->mailFactory->createNdrSubmissionConfirmationEmail($submittedByDeputy, $submittedNdr, $newReport)
        );
    }
}
