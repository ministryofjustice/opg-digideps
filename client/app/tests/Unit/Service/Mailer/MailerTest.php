<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Mailer;

use Faker\Factory;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Mail\EmailTemplate;
use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Common\Mail\NotifyMail;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Mailer\MailFactory;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class MailerTest extends TestCase
{
    public function testSendActivationEmail(): void
    {
        $activatedUser = UserHelpers::createUser();
        $activationEmail = $this->createEmail();

        $mailFactory = self::createMock(MailFactory::class);
        $mailSender = self::createMock(MailSenderInterface::class);

        $mailFactory->expects(self::once())->method('createActivationEmail')->with($activatedUser)->willReturn($activationEmail);
        $mailSender->expects(self::once())->method('send')->with($activationEmail);

        new Mailer($mailFactory, $mailSender, $this->createStub(MailSenderInterface::class))->sendActivationEmail($activatedUser);
    }

    #[DataProvider('deputyNameProvider')]
    public function testSendInvitationEmail(?string $deputyName): void
    {
        $invitedUser = UserHelpers::createUser();
        $invitationEmail = $this->createEmail();

        $mailFactory = self::createMock(MailFactory::class);
        $mailSender = self::createMock(MailSenderInterface::class);

        $mailFactory->expects(self::once())->method('createInvitationEmail')->with($invitedUser)->willReturn($invitationEmail);
        $mailSender->expects(self::once())->method('send')->with($invitationEmail);

        new Mailer($mailFactory, $mailSender, $this->createStub(MailSenderInterface::class))->sendInvitationEmail($invitedUser, $deputyName);
    }

    public static function deputyNameProvider(): array
    {
        return [
            'Name' => ['Poppy'],
            'Null' => [null],
        ];
    }

    public function testSendResetPasswordEmail(): void
    {
        $passwordResetUser = UserHelpers::createUser();
        $passwordResetEmail = $this->createEmail();

        $mailFactory = self::createMock(MailFactory::class);
        $mailSender = self::createMock(MailSenderInterface::class);

        $mailFactory->expects(self::once())->method('createResetPasswordEmailOld')->with($passwordResetUser)->willReturn($passwordResetEmail);
        $mailSender->expects(self::once())->method('send')->with($passwordResetEmail);

        new Mailer($mailFactory, $mailSender, $this->createStub(MailSenderInterface::class))->sendResetPasswordEmail($passwordResetUser);
    }

    public function testSendUpdateClientDetailsEmail(): void
    {
        $updatedClient = ClientHelpers::createClient();
        $updatedClientDetailsEmail = $this->createEmail();

        $mailFactory = self::createMock(MailFactory::class);
        $mailSender = self::createMock(MailSenderInterface::class);

        $mailFactory->expects(self::once())
            ->method('createUpdateClientDetailsEmail')
            ->with($updatedClient)
            ->willReturn($updatedClientDetailsEmail);

        $mailSender->expects(self::atLeastOnce())->method('send')->with($updatedClientDetailsEmail);

        new Mailer($mailFactory, $mailSender, $this->createStub(MailSenderInterface::class))->sendUpdateClientDetailsEmail($updatedClient);
    }

    public function testSendUpdateDeputyDetailsEmail(): void
    {
        $updatedDeputy = UserHelpers::createUser();
        $updatedDeputyDetailsEmail = $this->createEmail();

        $mailFactory = self::createMock(MailFactory::class);
        $mailSender = self::createMock(MailSenderInterface::class);

        $mailFactory->expects(self::once())
            ->method('createUpdateDeputyDetailsEmail')
            ->with($updatedDeputy)
            ->willReturn($updatedDeputyDetailsEmail);

        $mailSender->expects(self::atLeastOnce())->method('send')->with($updatedDeputyDetailsEmail);

        new Mailer($mailFactory, $mailSender, $this->createStub(MailSenderInterface::class))->sendUpdateDeputyDetailsEmail($updatedDeputy);
    }

    public function testSendReportSubmissionConfirmationEmail(): void
    {
        $submittedByDeputy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createSubmittedReport();
        $newReport = ReportHelpers::createReport();
        $submittedReportConfirmationEmail = $this->createEmail();

        $mailFactory = self::createMock(MailFactory::class);
        $mailSender = self::createMock(MailSenderInterface::class);

        $mailFactory->expects(self::once())
            ->method('createReportSubmissionConfirmationEmail')
            ->with($submittedByDeputy, $submittedReport, $newReport)
            ->willReturn($submittedReportConfirmationEmail);

        $mailSender->expects(self::atLeastOnce())->method('send')->with($submittedReportConfirmationEmail);

        new Mailer($mailFactory, $mailSender, $this->createStub(MailSenderInterface::class))->sendReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport);
    }

    private function createEmail(): NotifyMail
    {
        $faker = Factory::create();

        return new NotifyMail(
            new Email($faker->safeEmail()),
            EmailTemplate::Activation,
            ['a' => 'A' , 'b' => 'B'],
            Uuid::v4()
        );
    }
}
