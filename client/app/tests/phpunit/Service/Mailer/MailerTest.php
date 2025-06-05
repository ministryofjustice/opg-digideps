<?php

declare(strict_types=1);

namespace Tests\App\Service\Mailer;

use App\Service\Mailer\Mailer;
use App\Service\Mailer\MailFactory;
use App\Service\Mailer\MailSender;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\EmailHelpers;
use App\TestHelpers\NdrHelpers;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MailerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $mailFactory;

    /** @var ObjectProphecy */
    private $mailSender;

    /** @var Mailer */
    private $sut;

    public function setUp(): void
    {
        $this->mailFactory = self::prophesize(MailFactory::class);
        $this->mailSender = self::prophesize(MailSender::class);
        $this->sut = new Mailer($this->mailFactory->reveal(), $this->mailSender->reveal());
    }

    /** @test */
    public function sendActivationEmail()
    {
        $activatedUser = UserHelpers::createUser();
        $activationEmail = EmailHelpers::createEmail();

        $this->mailFactory->createActivationEmail($activatedUser)->shouldBeCalled()->willReturn($activationEmail);
        $this->mailSender->send($activationEmail)->shouldBeCalled();

        $this->sut->sendActivationEmail($activatedUser);
    }

    /**
     * @dataProvider deputyNameProvider
     *
     * @test
     */
    public function sendInvitationEmail(?string $deputyName)
    {
        $invitedUser = UserHelpers::createUser();
        $invitationEmail = EmailHelpers::createEmail();

        $this->mailFactory->createInvitationEmail($invitedUser, $deputyName)->shouldBeCalled()->willReturn($invitationEmail);
        $this->mailSender->send($invitationEmail)->shouldBeCalled();

        $this->sut->sendInvitationEmail($invitedUser, $deputyName);
    }

    public function deputyNameProvider()
    {
        return [
            'Name' => ['Poppy'],
            'Null' => [null],
        ];
    }

    /** @test */
    public function sendResetPasswordEmail()
    {
        $passwordResetUser = UserHelpers::createUser();
        $passwordResetEmail = EmailHelpers::createEmail();

        $this->mailFactory->createResetPasswordEmail($passwordResetUser)->shouldBeCalled()->willReturn($passwordResetEmail);
        $this->mailSender->send($passwordResetEmail)->shouldBeCalled();

        $this->sut->sendResetPasswordEmail($passwordResetUser);
    }

    /** @test */
    public function sendUpdateClientDetailsEmail()
    {
        $updatedClient = ClientHelpers::createClient();
        $updatedClientDetailsEmail = EmailHelpers::createEmail();

        $this->mailFactory
            ->createUpdateClientDetailsEmail($updatedClient)
            ->shouldBeCalled()
            ->willReturn($updatedClientDetailsEmail);

        $this->mailSender->send($updatedClientDetailsEmail)->shouldBeCalled();

        $this->sut->sendUpdateClientDetailsEmail($updatedClient);
    }

    /** @test */
    public function sendUpdateDeputyDetailsEmail()
    {
        $updatedDeputy = UserHelpers::createUser();
        $updatedDeputyDetailsEmail = EmailHelpers::createEmail();

        $this->mailFactory
            ->createUpdateDeputyDetailsEmail($updatedDeputy)
            ->shouldBeCalled()
            ->willReturn($updatedDeputyDetailsEmail);

        $this->mailSender->send($updatedDeputyDetailsEmail)->shouldBeCalled();

        $this->sut->sendUpdateDeputyDetailsEmail($updatedDeputy);
    }

    /** @test */
    public function sendReportSubmissionConfirmationEmail()
    {
        $submittedByDeputy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createSubmittedReport();
        $newReport = ReportHelpers::createReport();
        $submittedReportConfirmationEmail = EmailHelpers::createEmail();

        $this->mailFactory
            ->createReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport)
            ->shouldBeCalled()
            ->willReturn($submittedReportConfirmationEmail);

        $this->mailSender->send($submittedReportConfirmationEmail)->shouldBeCalled();

        $this->sut->sendReportSubmissionConfirmationEmail($submittedByDeputy, $submittedReport, $newReport);
    }

    /** @test */
    public function sendNdrSubmissionConfirmationEmail()
    {
        $submittedByDeputy = UserHelpers::createUser();
        $submittedNdr = NdrHelpers::createSubmittedNdr();
        $newReport = ReportHelpers::createReport();
        $submittedNdrConfirmationEmail = EmailHelpers::createEmail();

        $this->mailFactory
            ->createNdrSubmissionConfirmationEmail($submittedByDeputy, $submittedNdr, $newReport)
            ->shouldBeCalled()
            ->willReturn($submittedNdrConfirmationEmail);

        $this->mailSender->send($submittedNdrConfirmationEmail)->shouldBeCalled();

        $this->sut->sendNdrSubmissionConfirmationEmail($submittedByDeputy, $submittedNdr, $newReport);
    }
}
