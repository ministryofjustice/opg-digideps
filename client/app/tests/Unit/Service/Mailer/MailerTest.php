<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Mailer;

use Faker\Factory;
use OPG\Digideps\Frontend\Model\Email;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Mailer\MailFactory;
use OPG\Digideps\Frontend\Service\Mailer\MailSender;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{
    private MockObject&MailFactory $mailFactory;
    private MockObject&MailSender $mailSender;
    private Mailer $sut;

    public function setUp(): void
    {
        $this->mailFactory = $this->createMock(MailFactory::class);
        $this->mailSender = $this->createMock(MailSender::class);
        $this->sut = new Mailer($this->mailFactory, $this->mailSender);
    }

    public function testSendActivationEmail(): void
    {
        $activatedUser = UserHelpers::createUser();
        $activationEmail = $this->createEmail();

        $this->mailFactory->expects($this->once())->method('createActivationEmail')->with($activatedUser)->willReturn($activationEmail);
        $this->mailSender->expects($this->once())->method('send')->with($activationEmail);

        $this->sut->sendActivationEmail($activatedUser);
    }

    /**
     * @dataProvider deputyNameProvider
     */
    public function testSendInvitationEmail(?string $deputyName): void
    {
        $invitedUser = UserHelpers::createUser();
        $invitationEmail = $this->createEmail();

        $this->mailFactory->expects($this->once())->method('createInvitationEmail')->with($invitedUser)->willReturn($invitationEmail);
        $this->mailSender->expects($this->once())->method('send')->with($invitationEmail);

        $this->sut->sendInvitationEmail($invitedUser, $deputyName);
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

        $this->mailFactory->expects($this->once())->method('createResetPasswordEmail')->with($passwordResetUser)->willReturn($passwordResetEmail);
        $this->mailSender->expects($this->once())->method('send')->with($passwordResetEmail);

        $this->sut->sendResetPasswordEmail($passwordResetUser);
    }

    public function testSendUpdateClientDetailsEmail(): void
    {
        $updatedClient = ClientHelpers::createClient();
        $updatedClientDetailsEmail = $this->createEmail();

        $this->mailFactory
            ->expects($this->once())
            ->method('createUpdateClientDetailsEmail')
            ->with($updatedClient)
            ->willReturn($updatedClientDetailsEmail);

        $this->mailSender->expects($this->atLeastOnce())->method('send')->with($updatedClientDetailsEmail);

        $this->sut->sendUpdateClientDetailsEmail($updatedClient);
    }

    public function testSendUpdateDeputyDetailsEmail(): void
    {
        $updatedDeputy = UserHelpers::createUser();
        $updatedDeputyDetailsEmail = $this->createEmail();

        $this->mailFactory
            ->expects($this->once())
            ->method('createUpdateDeputyDetailsEmail')
            ->with($updatedDeputy)
            ->willReturn($updatedDeputyDetailsEmail);

        $this->mailSender->expects($this->atLeastOnce())->method('send')->with($updatedDeputyDetailsEmail);

        $this->sut->sendUpdateDeputyDetailsEmail($updatedDeputy);
    }

    public function testSendReportSubmissionConfirmationEmail(): void
    {
        $submittedByDeputy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createSubmittedReport();
        $newReport = ReportHelpers::createReport();
        $submittedReportConfirmationEmail = $this->createEmail();

        $this->mailFactory
            ->expects($this->once())
            ->method('createReportSubmissionConfirmationEmail')
            ->with($submittedByDeputy, $submittedReport, $newReport)
            ->willReturn($submittedReportConfirmationEmail);

        $this->mailSender->expects($this->atLeastOnce())->method('send')->with($submittedReportConfirmationEmail);

        $this->sut->sendReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport);
    }

    private function createEmail(): Email
    {
        $faker = Factory::create();

        return new Email()
            ->setFromEmailNotifyID($faker->uuid())
            ->setToEmail($faker->safeEmail())
            ->setFromName($faker->name())
            ->setSubject($faker->realText(35))
            ->setTemplate($faker->uuid())
            ->setParameters((array)$faker->words(3));
    }
}
