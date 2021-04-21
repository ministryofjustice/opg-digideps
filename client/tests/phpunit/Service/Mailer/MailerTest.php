<?php declare(strict_types=1);

namespace Tests\App\Service\Mailer;

use App\Model\Email;
use App\Model\FeedbackReport;
use App\Service\Mailer\Mailer;
use App\Service\Mailer\MailFactory;
use App\Service\Mailer\MailSender;
use App\TestHelpers\ClientHelper;
use App\TestHelpers\EmailHelper;
use App\TestHelpers\NdrHelper;
use App\TestHelpers\ReportHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserHelper;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class MailerTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $mailFactory;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $mailSender;

    /** @var Mailer */
    private $sut;

    public function setUp() : void
    {
        $this->mailFactory = self::prophesize(MailFactory::class);
        $this->mailSender = self::prophesize(MailSender::class);
        $this->sut = new Mailer($this->mailFactory->reveal(), $this->mailSender->reveal());
    }

    /** @test */
    public function sendActivationEmail()
    {
        $activatedUser = UserHelper::createUser();
        $activationEmail = EmailHelper::createEmail();

        $this->mailFactory->createActivationEmail($activatedUser)->shouldBeCalled()->willReturn($activationEmail);
        $this->mailSender->send($activationEmail)->shouldBeCalled();

        $this->sut->sendActivationEmail($activatedUser);
    }

    /**
     * @dataProvider deputyNameProvider
     * @test
     */
    public function sendInvitationEmail(?string $deputyName)
    {
        $invitedUser = UserHelper::createUser();
        $invitationEmail = EmailHelper::createEmail();

        $this->mailFactory->createInvitationEmail($invitedUser, $deputyName)->shouldBeCalled()->willReturn($invitationEmail);
        $this->mailSender->send($invitationEmail)->shouldBeCalled();

        $this->sut->sendInvitationEmail($invitedUser, $deputyName);
    }

    public function deputyNameProvider()
    {
        return [
            'Name' => ['Poppy'],
            'Null' => [null]
        ];
    }

    /** @test */
    public function sendResetPasswordEmail()
    {
        $passwordResetUser = UserHelper::createUser();
        $passwordResetEmail = EmailHelper::createEmail();

        $this->mailFactory->createResetPasswordEmail($passwordResetUser)->shouldBeCalled()->willReturn($passwordResetEmail);
        $this->mailSender->send($passwordResetEmail)->shouldBeCalled();

        $this->sut->sendResetPasswordEmail($passwordResetUser);
    }

    /** @test */
    public function sendGeneralFeedbackEmail()
    {
        $formResponse = [
            'specificPage' => true,
            'page' => null,
            'comments' => "Some comment here",
            'name' => "Shygirl",
            'email' => "shygirl@nuxxe.com",
            'phone' => "01211234567",
            'satisfactionLevel' => 5
        ];
        $generalFeedbackEmail = EmailHelper::createEmail();

        $this->mailFactory->createGeneralFeedbackEmail($formResponse)->shouldBeCalled()->willReturn($generalFeedbackEmail);
        $this->mailSender->send($generalFeedbackEmail)->shouldBeCalled();

        $this->sut->sendGeneralFeedbackEmail($formResponse);
    }

    /** @test */
    public function sendPostSubmissionFeedbackEmail()
    {
        $submittedFeedbackReport = (new FeedbackReport())
            ->setSatisfactionLevel(5)
            ->setComments('Some comments');

        $submittedByDeputy = UserHelper::createUser();
        $postSubmissionFeedbackEmail = EmailHelper::createEmail();

        $this->mailFactory
            ->createPostSubmissionFeedbackEmail($submittedFeedbackReport, $submittedByDeputy)
            ->shouldBeCalled()
            ->willReturn($postSubmissionFeedbackEmail);

        $this->mailSender->send($postSubmissionFeedbackEmail)->shouldBeCalled();

        $this->sut->sendPostSubmissionFeedbackEmail($submittedFeedbackReport, $submittedByDeputy);
    }

    /** @test */
    public function sendUpdateClientDetailsEmail()
    {
        $updatedClient = ClientHelper::createClient();
        $updatedClientDetailsEmail = EmailHelper::createEmail();

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
        $updatedDeputy = UserHelper::createUser();
        $updatedDeputyDetailsEmail = EmailHelper::createEmail();

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
        $submittedByDeputy = UserHelper::createUser();
        $submittedReport = ReportHelper::createSubmittedReport();
        $newReport = ReportHelper::createReport();
        $submittedReportConfirmationEmail = EmailHelper::createEmail();

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
        $submittedByDeputy = UserHelper::createUser();
        $submittedNdr = NdrHelper::createSubmittedNdr();
        $newReport = ReportHelper::createReport();
        $submittedNdrConfirmationEmail = EmailHelper::createEmail();

        $this->mailFactory
            ->createNdrSubmissionConfirmationEmail($submittedByDeputy, $submittedNdr, $newReport)
            ->shouldBeCalled()
            ->willReturn($submittedNdrConfirmationEmail);

        $this->mailSender->send($submittedNdrConfirmationEmail)->shouldBeCalled();

        $this->sut->sendNdrSubmissionConfirmationEmail($submittedByDeputy, $submittedNdr, $newReport);
    }
}
