<?php declare(strict_types=1);

namespace AppBundle\Service\Mailer;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Model\FeedbackReport;
use AppBundle\Service\IntlService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Bundle\TwigBundle\TwigEngine;

class MailFactoryTest extends TestCase
{
    /** @var ObjectProphecy&Translator */
    private $translator;

    /** @var ObjectProphecy&Router */
    private $router;

    /** @var ObjectProphecy&TwigEngine */
    private $templating;

    /** @var ObjectProphecy&IntlService */
    private $intlService;

    /** @var User */
    private $layDeputy;

    /** @var array */
    private $appBaseURLs;

    /** @var array */
    private $emailSendParams;

    /** @var Client */
    private $client;

    /** @var Report */
    private $submittedReport;

    /** @var Report */
    private $newReport;

    public function setUp(): void
    {
        $this->client = $this->generateClient();

        $this->layDeputy = $this->generateUser();
        $this->layDeputy->setClients([$this->client]);

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
            'update_send_to_address' => 'updateAddress@digital.justice.gov.uk'
        ];

        $this->translator = self::prophesize('Symfony\Bundle\FrameworkBundle\Translation\Translator');
        $this->router = self::prophesize('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->templating = self::prophesize('Symfony\Bundle\TwigBundle\TwigEngine');
        $this->intlService = self::prophesize('AppBundle\Service\IntlService');
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

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $expectedTemplateParams = array_merge($this->getContactParameters(), [
            'activationLink' => 'https://front.base.url/activate/regToken',
        ]);

        $email = ($this->generateSUT())->createActivationEmail($this->layDeputy);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::ACTIVATION_TEMPLATE_ID, $email->getTemplate());
        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function profActivationEmailHasProfContacts()
    {
        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/activate/regToken');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('profSupportEmail', [], 'common')->shouldBeCalled()->willReturn('prof-email@publicguardian.gov.uk');
        $this->translator->trans('helplineProf', [], 'common')->shouldBeCalled()->willReturn('07987654321');

        $profDeputy = $this->generateUser()->setRoleName(User::ROLE_PROF_ADMIN);

        $email = ($this->generateSUT())->createActivationEmail($profDeputy);

        self::assertEquals('prof-email@publicguardian.gov.uk', $email->getParameters()['email']);
        self::assertEquals('07987654321', $email->getParameters()['phone']);
    }

    /**
     * @test
     */
    public function paActivationEmailHasPaContacts()
    {
        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/activate/regToken');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('paSupportEmail', [], 'common')->shouldBeCalled()->willReturn('pa-email@publicguardian.gov.uk');
        $this->translator->trans('helplinePA', [], 'common')->shouldBeCalled()->willReturn('07777777777');

        $paDeputy = $this->generateUser()->setRoleName(User::ROLE_PA_ADMIN);

        $email = ($this->generateSUT())->createActivationEmail($paDeputy);

        self::assertEquals('pa-email@publicguardian.gov.uk', $email->getParameters()['email']);
        self::assertEquals('07777777777', $email->getParameters()['phone']);
    }

    /**
     * @test
     */
    public function createInvitationEmailLayUser()
    {
        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/activate/regToken');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');

        $expectedTemplateParams = array_merge($this->getContactParameters(), [
            'link' => 'https://front.base.url/activate/regToken',
            'deputyName' => 'Buford Mcfarling'
        ]);

        $email = ($this->generateSUT())->createInvitationEmail($this->layDeputy, 'Buford Mcfarling');

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::INVITATION_LAY_TEMPLATE_ID, $email->getTemplate());
        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createInvitationEmailOrgUser()
    {
        $profDeputy = $this->generateUser()
            ->setEmail('l.wolny@somesolicitors.org')
            ->setRoleName('ROLE_PROF_TEAM_MEMBER');

        $this->router->generate('user_activate', [
            'action' => 'activate',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/activate/regToken');

        $this->translator->trans('activation.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('profSupportEmail', [], 'common')->shouldBeCalled()->willReturn('prof-email@publicguardian.gov.uk');
        $this->translator->trans('helplineProf', [], 'common')->shouldBeCalled()->willReturn('07987654321');

        $expectedTemplateParams = [
            'link' => 'https://front.base.url/activate/regToken',
            'email' => 'prof-email@publicguardian.gov.uk',
            'phone' => '07987654321'
        ];

        $email = ($this->generateSUT())->createInvitationEmail($profDeputy);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('l.wolny@somesolicitors.org', $email->getToEmail());
        self::assertEquals(MailFactory::INVITATION_ORG_TEMPLATE_ID, $email->getTemplate());
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
            'startDate' => '24 March 2017',
            'endDate' => '23 March 2018',
            'homepageURL' => 'https://front.base.url',
            'newStartDate' => '24 March 2018',
            'newEndDate' => '23 March 2019',
            'EndDatePlus1' => '24 March 2019',
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
            'startDate' => '24 March 2017',
            'endDate' => '23 March 2018',
            'homepageURL' => 'https://front.base.url',
            'newStartDate' => '24 March 2018',
            'newEndDate' => '23 March 2019',
            'EndDatePlus1' => '24 March 2019',
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
            'startDate' => '24 March 2018',
            'endDate' => '23 March 2019',
            'EndDatePlus1' => '24 March 2019',
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

        $expectedTemplateParams = array_merge($this->getContactParameters(), [
            'resetLink' => 'https://front.base.url/reset-password/regToken',
            'recreateLink' => 'https://front.base.url/password-managing/forgotten',
        ]);

        $email = ($this->generateSUT())->createResetPasswordEmail($this->layDeputy);

        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
        self::assertEquals('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::RESET_PASSWORD_TEMPLATE_ID, $email->getTemplate());
        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createGeneralFeedbackEmail()
    {
        $this->translator->trans('feedbackForm.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
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

        $this->assertStaticEmailProperties($email);

        self::assertEquals('digideps+noop@digital.justice.gov.uk', $email->getToEmail());
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
        $this->translator->trans('feedbackForm.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $response = (new FeedbackReport())
            ->setComments('Amazing service!')
            ->setSatisfactionLevel('4');

        $email = ($this->generateSUT())->createPostSubmissionFeedbackEmail($response, $this->generateUser());

        $this->assertStaticEmailProperties($email);

        self::assertEquals('digideps+noop@digital.justice.gov.uk', $email->getToEmail());
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

    /**
     * @test
     */
    public function createUpdateClientDetailsEmail()
    {
        $this->translator->trans('client.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('client.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $client = $this->generateClient();

        $email = ($this->generateSUT())->createUpdateClientDetailsEmail($client);

        $this->assertStaticEmailProperties($email);

        self::assertEquals('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::CLIENT_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'caseNumber' => '12345678',
            'fullName' => 'Joanne Bloggs',
            'address' => '10 Fake Road',
            'address2' => 'Pretendville',
            'address3' => 'Notrealingham',
            'postcode' => 'A12 3BC',
            'countryName' => 'United Kingdom',
            'phone' => '01215553333',
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createUpdateClientDetailsEmail_country_not_set()
    {
        $this->translator->trans('client.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('client.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $client = $this->generateClient()->setCountry(null);

        $email = ($this->generateSUT())->createUpdateClientDetailsEmail($client);

        $this->assertStaticEmailProperties($email);

        self::assertEquals('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::CLIENT_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'caseNumber' => '12345678',
            'fullName' => 'Joanne Bloggs',
            'address' => '10 Fake Road',
            'address2' => 'Pretendville',
            'address3' => 'Notrealingham',
            'postcode' => 'A12 3BC',
            'countryName' => 'Country not provided',
            'phone' => '01215553333',
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createUpdateDeputyDetailsEmail()
    {
        $this->translator->trans('client.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('client.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $email = ($this->generateSUT())->createUpdateDeputyDetailsEmail($this->layDeputy);

        $this->assertStaticEmailProperties($email);

        self::assertEquals('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::DEPUTY_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'caseNumber' => '12345678',
            'fullName' => 'Joe Bloggs',
            'address' => '10 Fake Road',
            'address2' => 'Pretendville',
            'address3' => 'Notrealingham',
            'postcode' => 'A12 3BC',
            'countryName' => 'United Kingdom',
            'phone' => '01211234567',
            'altPhoneNumber' => '01217654321',
            'email' => 'user@digital.justice.gov.uk'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    /**
     * @test
     */
    public function createUpdateDeputyDetailsEmail_country_not_set()
    {
        $this->translator->trans('client.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('client.subject', [], 'email')->shouldBeCalled()->willReturn('A subject');

        $email = ($this->generateSUT())->createUpdateDeputyDetailsEmail($this->layDeputy->setAddressCountry(null));

        $this->assertStaticEmailProperties($email);

        self::assertEquals('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        self::assertEquals(MailFactory::DEPUTY_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

        $expectedTemplateParams = [
            'caseNumber' => '12345678',
            'fullName' => 'Joe Bloggs',
            'address' => '10 Fake Road',
            'address2' => 'Pretendville',
            'address3' => 'Notrealingham',
            'postcode' => 'A12 3BC',
            'countryName' => 'Country not provided',
            'phone' => '01211234567',
            'altPhoneNumber' => '01217654321',
            'email' => 'user@digital.justice.gov.uk'
        ];

        self::assertEquals($expectedTemplateParams, $email->getParameters());
    }

    private function assertStaticEmailProperties($email)
    {
        self::assertEquals(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        self::assertEquals('OPG', $email->getFromName());
    }

    private function generateSUT()
    {
        return new MailFactory(
            $this->translator->reveal(),
            $this->router->reveal(),
            $this->templating->reveal(),
            new IntlService(),
            $this->emailSendParams,
            $this->appBaseURLs
        );
    }

    private function generateUser($role = User::ROLE_LAY_DEPUTY) : User
    {
        return (new User())
            ->setRegistrationToken('regToken')
            ->setEmail('user@digital.justice.gov.uk')
            ->setFirstname('Joe')
            ->setLastname('Bloggs')
            ->setPhoneMain('01211234567')
            ->setPhoneAlternative('01217654321')
            ->setAddress1('10 Fake Road')
            ->setAddress2('Pretendville')
            ->setAddressPostcode('A12 3BC')
            ->setAddress3('Notrealingham')
            ->setAddressCountry('GB')
            ->setRoleName($role);
    }

    private function generateClient() : Client
    {
        return (new Client())
            ->setFirstname('Joanne')
            ->setLastname('Bloggs')
            ->setCaseNumber('12345678')
            ->setAddress('10 Fake Road')
            ->setAddress2('Pretendville')
            ->setPostcode('A12 3BC')
            ->setCounty('Notrealingham')
            ->setCountry('GB')
            ->setPhone('01215553333');
    }

    private function getContactParameters(): array
    {
        $this->translator->trans('layDeputySupportEmail', [], 'common')->shouldBeCalled()->willReturn('help-email@publicguardian.gov.uk');
        $this->translator->trans('helpline', [], 'common')->shouldBeCalled()->willReturn('0123456789');

        return [
            'email' => 'help-email@publicguardian.gov.uk',
            'phone' => '0123456789',
        ];
    }
}
