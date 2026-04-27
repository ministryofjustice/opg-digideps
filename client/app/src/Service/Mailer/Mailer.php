<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Mailer;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\ReportInterface;
use OPG\Digideps\Frontend\Entity\User;

class Mailer
{
    /** @var MailFactory */
    private MailFactory $mailFactory;

    /** @var MailSender */
    private MailSender $mailSender;

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

    /**
     * @throws \Exception
     */
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

    /**
     * @throws \Exception
     */
    public function sendReportSubmissionConfirmationEmail(
        User $submittedByDeputy,
        ReportInterface $submittedReport,
        Report $newReport,
    ): bool {
        return $this->mailSender->send(
            $this->mailFactory->createReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport)
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
