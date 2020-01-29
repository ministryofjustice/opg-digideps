<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Bundle\TwigBundle\TwigEngine;

class MailFactoryTest extends TestCase
{
    /**
     * @var MailFactory
     */
    private $object;

    /**
     * @var User
     */
    private $layDeputy;

    /**
     * @var array
     */
    private $appBaseURLs;

    /**
     * @var array
     */
    private $emailSendParams;

    /**
     * @var ObjectProphecy&Translator
     */
    private $translator;

    /**
     * @var ObjectProphecy&Router
     */
    private $router;

    /**
     * @var ObjectProphecy&TwigEngine
     */
    private $templating;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Report
     */
    private $submittedReport;

    /**
     * @var Report
     */
    private $newReport;

    public function setUp(): void
    {
        $this->layDeputy = $this->generateUser();

        $this->client = (new Client())
            ->setFirstname('Joanne')
            ->setLastname('Bloggs')
            ->setCaseNumber('12345678');

        $this->submittedReport = (new Report())
            ->setClient($this->client);

        $this->newReport = new Report();

        $this->appBaseURLs = [
            'front' => 'https://front.base.url',
            'admin' => 'https://admin.base.url'
        ];

        $this->emailSendParams = [
            'from_email' => 'digideps+from@digital.justice.gov.uk',
            'report_submit_to_address' => 'digideps+noop@digital.justice.gov.uk',
            'feedback_send_to_address' => 'digideps+noop@digital.justice.gov.uk',
            'update_send_to_address' => 'digideps+noop@digital.justice.gov.uk'
        ];

        $this->translator = self::prophesize('Symfony\Bundle\FrameworkBundle\Translation\Translator');
        $this->router = self::prophesize('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->templating = self::prophesize('Symfony\Bundle\TwigBundle\TwigEngine');
    }

    /**
     * @test
     */
    public function createActivationEmail()
    {
        $this->router->generate('homepage', [])->shouldBeCalled()->willReturn('/homepage');
        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/user-activate/regToken');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('activation.subject', [], 'email')->shouldBeCalled()->willReturn('Activation Subject');

        $expectedViewParams = [
            'name'             => 'Joe Bloggs',
            'domain'           => 'https://front.base.url/homepage',
            'link'             => 'https://front.base.url/user-activate/regToken',
            'tokenExpireHours' => 48,
            'homepageUrl'      => 'https://front.base.url/homepage',
            'recipientRole'    => 'default'
        ];

        $this->templating->render('AppBundle:Email:user-activate.html.twig', $expectedViewParams)->shouldBeCalled()->willReturn('<html>Rendered body</html>');
        $this->templating->render('AppBundle:Email:user-activate.text.twig', $expectedViewParams)->shouldBeCalled()->willReturn('Rendered body');

        $email = ($this->generateSUT())->createActivationEmail($this->layDeputy);

        self::assertEquals('digideps+from@digital.justice.gov.uk', $email->getFromEmail());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals('Joe Bloggs', $email->getToName());
        self::assertEquals('Activation Subject', $email->getSubject());
        self::assertStringContainsString('<html>Rendered body</html>', $email->getBodyHtml());
        self::assertStringContainsString('Rendered body', $email->getBodyText());
    }

    /**
     * @test
     */
    public function createOrgReportSubmissionConfirmationEmail()
    {
        $this->router->generate('homepage', [])->shouldBeCalled()->willReturn('/homepage');

        $this->translator->trans('reportSubmissionConfirmation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('reportSubmissionConfirmation.subject', ['%clientFullname%' => 'Joanne Bloggs'], 'email')->shouldBeCalled()->willReturn('Submission Confirmation Subject');

        $expectedViewParams = [
            'submittedReport' => $this->submittedReport,
            'newReport'       => $this->newReport,
            'fullDeputyName'  => 'Joe Bloggs',
            'fullClientName'  => 'Joanne Bloggs',
            'caseNumber'      => '12345678',
            'homepageUrl'     => 'https://front.base.url/homepage',
            'recipientRole'   => 'default'
        ];

        $this->templating->render('AppBundle:Email:report-submission-confirm.html.twig', $expectedViewParams)->shouldBeCalled()->willReturn('<html>Rendered body</html>');
        $this->templating->render('AppBundle:Email:report-submission-confirm.text.twig', $expectedViewParams)->shouldBeCalled()->willReturn('Rendered body');

        $email = ($this->generateSUT())->createOrgReportSubmissionConfirmationEmail($this->layDeputy, $this->submittedReport, $this->newReport);

        self::assertEquals('digideps+from@digital.justice.gov.uk', $email->getFromEmail());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals('Joe', $email->getToName());
        self::assertEquals('Submission Confirmation Subject', $email->getSubject());
        self::assertStringContainsString('<html>Rendered body</html>', $email->getBodyHtml());
        self::assertStringContainsString('Rendered body', $email->getBodyText());
    }

    /**
     * @todo rename once we drop Notify from the end of the function
     * @test
     */
    public function createResetPasswordEmailNotify()
    {
        $this->router->generate('user_activate', [
            'action' => 'password-reset',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/reset-password/regToken');

        $this->translator->trans('resetPassword.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('resetPassword.subject', [], 'email')->shouldBeCalled()->willReturn('Reset Password Subject');

        $email = ($this->generateSUT())->createResetPasswordEmailNotify($this->layDeputy);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals('Joe Bloggs', $email->getToName());
        self::assertEquals('Reset Password Subject', $email->getSubject());
        self::assertEquals(MailFactory::RESET_PASSWORD_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = ['resetLink' => 'https://front.base.url/reset-password/regToken'];
        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     * @dataProvider feedbackEmailDataProvider
     */
    public function createFeedbackEmail(bool $isPostSubmission, ?User $user, string $expectedTemplateID, ?string $expectedRoleName)
    {
        $this->translator->trans('feedbackForm.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('feedbackForm.toName', [], 'email')->shouldBeCalled()->willReturn('To Name');
        $this->translator->trans('feedbackForm.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $response = [
                'specificPage' => 'A specific page',
                'page' => 'A page',
                'comments' => 'It was great!',
                'name' => 'Joe Bloggs',
                'email' => 'joe.bloggs@xyz.com',
                'phone' => '07535999222',
                'satisfactionLevel' => '4',
        ];

        $email = ($this->generateSUT())->createFeedbackEmail($response, $isPostSubmission, $user);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('digideps+noop@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals('To Name', $email->getToName());
        self::assertEquals($expectedTemplateID, $email->getTemplate());

        $expectedTemplateParams = [
            'comments' => 'It was great!',
            'satisfactionLevel' => '4',
            'name' => 'Joe Bloggs',
            'phone' => '07535999222',
            'page' => 'A page',
            'userRole' => $expectedRoleName,
            'email' => 'joe.bloggs@xyz.com',
            'subject' => 'A subject'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    public function feedbackEmailDataProvider()
    {
        return [
            'postSubmission' => [true, $this->generateUser(), MailFactory::POST_SUBMISSION_FEEDBACK_TEMPLATE_ID, 'Lay Deputy'],
            'general' => [false, null, MailFactory::GENERAL_FEEDBACK_TEMPLATE_ID, 'Not provided'],
        ];
    }

    private function generateSUT()
    {
        return new MailFactory(
            $this->translator->reveal(),
            $this->router->reveal(),
            $this->templating->reveal(),
            $this->emailSendParams,
            $this->appBaseURLs
        );
    }

    // Using helper function to make user available in dataProvider
    private function generateUser()
    {
        return (new User())
            ->setRegistrationToken('regToken')
            ->setEmail('user@digital.justice.gov.uk')
            ->setFirstname('Joe')
            ->setLastname('Bloggs')
            ->setRoleName(User::ROLE_LAY_DEPUTY);
    }
}
