<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\ReportInterface;
use App\Entity\User;

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

    public function sendActivationEmail(User $activatedUser): bool
    {
        return $this->mailSender->send($this->mailFactory->createActivationEmail($activatedUser));
    }

    public function sendInvitationEmail(User $invitedUser, ?string $deputyName = null): bool
    {
        return $this->mailSender->send($this->mailFactory->createInvitationEmail($invitedUser, $deputyName));
    }

    public function sendResetPasswordEmail(User $passwordResetUser): bool
    {
        return $this->mailSender->send($this->mailFactory->createResetPasswordEmail($passwordResetUser));
    }

    public function sendUpdateClientDetailsEmail(Client $updatedClient): bool
    {
        return $this->mailSender->send($this->mailFactory->createUpdateClientDetailsEmail($updatedClient));
    }

    public function sendUpdateDeputyDetailsEmail(User $updatedDeputy): bool
    {
        return $this->mailSender->send($this->mailFactory->createUpdateDeputyDetailsEmail($updatedDeputy));
    }

    public function sendReportSubmissionConfirmationEmail(
        User $submittedByDeputy,
        ReportInterface $submittedReport,
        Report $newReport,
    ): bool {
        return $this->mailSender->send(
            $this->mailFactory->createReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport)
        );
    }

    public function sendNdrSubmissionConfirmationEmail(
        User $submittedByDeputy,
        Ndr $submittedNdr,
        Report $newReport,
    ): bool {
        return $this->mailSender->send(
            $this->mailFactory->createNdrSubmissionConfirmationEmail($submittedByDeputy, $submittedNdr, $newReport)
        );
    }

    public function sendProcessOrgCSVEmail(string $adminUser, array $output): bool
    {
        return $this->mailSender->send(
            $this->mailFactory->createProcessOrgCSVEmail($adminUser, $output)
        );
    }

    public function sendProcessLayCSVEmail(string $adminUser, array $output): bool
    {
        return $this->mailSender->send(
            $this->mailFactory->createProcessLayCSVEmail($adminUser, $output)
        );
    }
}
