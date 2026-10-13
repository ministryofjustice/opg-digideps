<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Mailer;

use OPG\Digideps\Common\Account\AccountResponse;
use OPG\Digideps\Common\Account\PasswordResponse;
use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class Mailer
{
    public function __construct(
        private MailFactory $mailFactory,
        #[Autowire(service: MailSender::class)]
        private MailSenderInterface $mailSender,
        #[Autowire(service: MailApi::class)]
        private MailSenderInterface $mailApi,
    ) {
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
        return $this->mailSender->send($this->mailFactory->createResetPasswordEmailOld($passwordResetUser));
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
        Report $submittedReport,
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

    public function sendSetPasswordEmail(AccountResponse $response, bool $isReset): bool
    {
        return $this->mailApi->send(
            $isReset ? $this->mailFactory->createResetPasswordEmail($response) : $this->mailFactory->createSetPasswordEmail($response)
        );
    }

    public function sendConfirmPasswordEmail(PasswordResponse $response): bool
    {
        return $this->mailApi->send(
            $this->mailFactory->createConfirmPasswordEmail($response)
        );
    }
}
