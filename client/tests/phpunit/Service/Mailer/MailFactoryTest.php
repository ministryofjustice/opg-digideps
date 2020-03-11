<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Model\FeedbackReport;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
            ->setClient($this->client)
            ->setStartDate(new \DateTime('2017-03-24'))
            ->setEndDate(new \DateTime('2018-03-23'));

        $this->newReport = (new Report())
            ->setStartDate(new \DateTime('2018-03-24'))
            ->setEndDate(new \DateTime('2019-03-23'));

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
        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/activate/regToken');

        $this->router->generate('register', [])
            ->shouldBeCalled()
            ->willReturn('/register');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $email = ($this->generateSUT())->createActivationEmail($this->layDeputy);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::ACTIVATION_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'activationLink' => 'https://front.base.url/activate/regToken',
            'registerLink' => 'https://front.base.url/register',
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createInvitationEmail()
    {
        $profDeputy = $this->generateUser()
            ->setEmail('l.wolny@somesolicitors.org')
            ->setRoleName('ROLE_PROF_TEAM_MEMBER');

        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/activate/regToken');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $email = ($this->generateSUT())->createInvitationEmail($profDeputy, 'Buford Mcfarling');

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('l.wolny@somesolicitors.org', $email->getToEmail());
        self::assertEquals(MailFactory::INVITATION_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'link' => 'https://front.base.url/activate/regToken',
            'deputyName' => 'Buford Mcfarling'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     * @dataProvider getLayReportTypes
     */
    public function createReportSubmissionConfirmationEmailForLayDeputy($reportType)
    {
        $this->translator->trans('reportSubmissionConfirmation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans(Argument::any())->shouldNotBeCalled();

        $this->submittedReport->setType($reportType);
        $email = ($this->generateSUT())->createReportSubmissionConfirmationEmail($this->layDeputy, $this->submittedReport, $this->newReport);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals(MailFactory::REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID, $email->getTemplate());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());

        $expectedTemplateParams = [
            'clientFullname' => 'Joanne Bloggs',
            'deputyFullname' => 'Joe Bloggs',
            'orgIntro' => '',
            'startDate' => '24/03/2017',
            'endDate' => '23/03/2018',
            'homepageURL' => 'https://front.base.url',
            'newStartDate' => '24/03/2018',
            'newEndDate' => '23/03/2019',
            'EndDatePlus1' => '24/03/2019',
            'PFA' => substr($reportType, 0, 3 ) === '104' ? 'no' : 'yes',
            'lay' => 'yes'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    public function getLayReportTypes(): array
    {
        return [
            ['reportType' => '102'],
            ['reportType' => '103'],
            ['reportType' => '102-4'],
            ['reportType' => '103-4'],
            ['reportType' => '104'],
        ];
    }

    /**
     * @test
     * @dataProvider getOrgReportTypes
     */
    public function createReportSubmissionConfirmationEmailForOrgDeputy($reportType, $role)
    {
        $this->translator->trans('reportSubmissionConfirmation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $clientFullName = $this->client->getFullname();
        $caseNumber = $this->client->getCaseNumber();
        $this->translator
            ->trans('caseDetails', ['%fullClientName%' => $clientFullName, '%caseNumber%' => $caseNumber], 'email-report-submission-confirm')
            ->shouldBeCalled()
            ->willReturn('Client: Joanne Bloggs Case number: 12345678');

        $this->submittedReport->setType($reportType);
        $deputy = $this->generateUser($role);
        $email = ($this->generateSUT())->createReportSubmissionConfirmationEmail($deputy, $this->submittedReport, $this->newReport);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals(MailFactory::REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID, $email->getTemplate());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());

        $expectedTemplateParams = [
            'clientFullname' => 'Joanne Bloggs',
            'deputyFullname' => 'Joe Bloggs',
            'orgIntro' => 'Client: Joanne Bloggs Case number: 12345678',
            'startDate' => '24/03/2017',
            'endDate' => '23/03/2018',
            'homepageURL' => 'https://front.base.url',
            'newStartDate' => '24/03/2018',
            'newEndDate' => '23/03/2019',
            'EndDatePlus1' => '24/03/2019',
            'PFA' => substr($reportType, 0, 3 ) === '104' ? 'no' : 'yes',
            'lay' => 'no'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    public function getOrgReportTypes(): array
    {
        return [
            ['reportType' => '102-5', 'role' => User::ROLE_PROF_NAMED],
            ['reportType' => '103-5', 'role' => User::ROLE_PROF_NAMED],
            ['reportType' => '102-5-4', 'role' => User::ROLE_PROF_NAMED],
            ['reportType' => '103-5-4', 'role' => User::ROLE_PROF_NAMED],
            ['reportType' => '104-5', 'role' => User::ROLE_PROF_NAMED],
            ['reportType' => '102-6', 'role' => User::ROLE_PA_NAMED],
            ['reportType' => '103-6', 'role' => User::ROLE_PA_NAMED],
            ['reportType' => '102-6-4', 'role' => User::ROLE_PA_NAMED],
            ['reportType' => '103-6-4', 'role' => User::ROLE_PA_NAMED],
            ['reportType' => '104-6', 'role' => User::ROLE_PA_NAMED],
        ];
    }

    /**
     * @test
     */
    public function createNdrSubmissionConfirmationEmailTest()
    {
        $this->translator->trans('ndrSubmissionConfirmation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $ndr = (new Ndr())->setClient($this->client);
        $email = ($this->generateSUT())->createNdrSubmissionConfirmationEmail($this->layDeputy, $ndr, $this->newReport);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals(MailFactory::NDR_SUBMITTED_CONFIRMATION_TEMPLATE_ID, $email->getTemplate());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());

        $expectedTemplateParams = [
            'clientFullname' => 'Joanne Bloggs',
            'deputyFullname' => 'Joe Bloggs',
            'homepageURL' => 'https://front.base.url',
            'startDate' => '24/03/2018',
            'endDate' => '23/03/2019',
            'EndDatePlus1' => '24/03/2019',
            'PFA' => 'yes',
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createResetPasswordEmail()
    {
        $this->router->generate('user_activate', [
            'action' => 'password-reset',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/reset-password/regToken');

        $this->router->generate('password_forgotten', [])
            ->shouldBeCalled()
            ->willReturn('/password-managing/forgotten');

        $this->translator->trans('resetPassword.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $email = ($this->generateSUT())->createResetPasswordEmail($this->layDeputy);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::RESET_PASSWORD_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'resetLink' => 'https://front.base.url/reset-password/regToken',
            'recreateLink' => 'https://front.base.url/password-managing/forgotten',
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createGeneralFeedbackEmail()
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

        $email = ($this->generateSUT())->createGeneralFeedbackEmail($response);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('digideps+noop@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals('To Name', $email->getToName());
        self::assertEquals(MailFactory::GENERAL_FEEDBACK_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'comments' => 'It was great!',
            'satisfactionLevel' => '4',
            'name' => 'Joe Bloggs',
            'phone' => '07535999222',
            'page' => 'A page',
            'email' => 'joe.bloggs@xyz.com',
            'subject' => 'A subject'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createPostSubmissionFeedbackEmail()
    {
        $this->translator->trans('feedbackForm.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('feedbackForm.toName', [], 'email')->shouldBeCalled()->willReturn('To Name');
        $this->translator->trans('feedbackForm.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $response = (new FeedbackReport())
            ->setComments('Amazing service!')
            ->setSatisfactionLevel('4');

        $user = $this->generateUser();

        $email = ($this->generateSUT())->createPostSubmissionFeedbackEmail($response, $this->generateUser());

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('digideps+noop@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals('To Name', $email->getToName());
        self::assertEquals(MailFactory::POST_SUBMISSION_FEEDBACK_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'comments' => 'Amazing service!',
            'satisfactionLevel' => '4',
            'name' => 'Joe Bloggs',
            'phone' => '01211234567',
            'email' => 'user@digital.justice.gov.uk',
            'subject' => 'A subject',
            'userRole' => 'Lay Deputy'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
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
    private function generateUser($role = User::ROLE_LAY_DEPUTY) : User
    {
        return (new User())
            ->setRegistrationToken('regToken')
            ->setEmail('user@digital.justice.gov.uk')
            ->setFirstname('Joe')
            ->setLastname('Bloggs')
            ->setPhoneMain('01211234567')
            ->setPhoneAlternative('01217654321')
            ->setRoleName($role);
    }
}
