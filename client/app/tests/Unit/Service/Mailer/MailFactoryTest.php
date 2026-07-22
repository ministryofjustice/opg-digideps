<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Mailer;

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Service\IntlService;
use OPG\Digideps\Frontend\Service\Mailer\MailFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailFactoryTest extends TestCase
{
    private MockObject&TranslatorInterface $translator;
    private MockObject&RouterInterface $router;
    private User $layDeputy;
    private Client $client;
    private Report $submittedReport;
    private Report $newReport;

    public function setUp(): void
    {
        $this->client = $this->generateClient();

        $this->layDeputy = $this->generateUser();
        $this->layDeputy->setClients([$this->client]);

        $this->submittedReport = new Report()
            ->setClient($this->client)
            ->setStartDate(new \DateTime('2017-03-24'))
            ->setEndDate(new \DateTime('2018-03-23'));

        $this->newReport = new Report()
            ->setStartDate(new \DateTime('2018-03-24'))
            ->setEndDate(new \DateTime('2019-03-23'));

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    private function generateClient(): Client
    {
        return new Client()
            ->setFirstname('Joanne')
            ->setLastname('Bloggs')
            ->setCaseNumber('12345678')
            ->setAddress('10 Fake Road')
            ->setAddress2('Pretendville')
            ->setPostcode('A12 3BC')
            ->setAddress3('Notrealingham')
            ->setCountry('GB')
            ->setPhone('01215553333');
    }

    private function generateUser($role = User::ROLE_LAY_DEPUTY): User
    {
        return new User()
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

    private function assertStaticEmailProperties($email): void
    {
        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame('OPG', $email->getFromName());
    }

    private function getContactParameters(): array
    {
        return [
            'email' => 'help-email@publicguardian.gov.uk',
            'phone' => '0123456789',
        ];
    }

    private function generateSUT(): MailFactory
    {
        return new MailFactory(
            $this->translator,
            $this->router,
            new IntlService(),
            [
                'from_email' => 'digideps+from@digital.justice.gov.uk',
                'report_submit_to_address' => 'digideps+noop@digital.justice.gov.uk',
                'feedback_send_to_address' => 'digideps+noop@digital.justice.gov.uk',
                'update_send_to_address' => 'updateAddress@digital.justice.gov.uk',
            ],
            [
                'front' => 'https://front.base.url',
                'admin' => 'https://admin.base.url',
            ]
        );
    }

    public function testCreateActivationEmail(): void
    {
        $this->router->expects($this->once())->method('generate')->with('user_activate', [
            'action' => 'activate',
            'token' => 'regToken',
        ])->willReturn('/activate/regToken');

        $this->translator->expects($this->exactly(3))->method('trans')->willReturnMap([
            ['layDeputySupportEmail', [], 'common', null, 'help-email@publicguardian.gov.uk'],
            ['helpline', [], 'common', null, '0123456789'],
            ['activation.fromName', [], 'email', null, 'OPG']
        ]);

        $expectedTemplateParams = array_merge($this->getContactParameters(), [
            'activationLink' => 'https://front.base.url/activate/regToken',
        ]);

        $email = $this->generateSUT()->createActivationEmail($this->layDeputy);

        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame('OPG', $email->getFromName());
        $this->assertSame('user@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::ACTIVATION_TEMPLATE_ID, $email->getTemplate());
        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testProfActivationEmailHasProfContacts(): void
    {
        $this->router->expects($this->once())->method('generate')->with('user_activate', [
            'action' => 'activate',
            'token' => 'regToken',
        ])->willReturn('/activate/regToken');

        $this->translator->expects($this->exactly(3))->method('trans')->willReturnMap([
            ['profSupportEmail', [], 'common', null, 'prof-email@publicguardian.gov.uk'],
            ['helpline', [], 'common', null, '07987654321'],
            ['activation.fromName', [], 'email', null, 'OPG']
        ]);

        $profDeputy = $this->generateUser()->setRoleName(User::ROLE_PROF_ADMIN);

        $email = $this->generateSUT()->createActivationEmail($profDeputy);

        $this->assertSame('prof-email@publicguardian.gov.uk', $email->getParameters()['email'] ?? null);
        $this->assertSame('07987654321', $email->getParameters()['phone'] ?? null);
    }

    public function testPaActivationEmailHasPaContacts(): void
    {
        $this->router->expects($this->once())->method('generate')->with('user_activate', [
            'action' => 'activate',
            'token' => 'regToken',
        ])->willReturn('/activate/regToken');

        $this->translator->expects($this->exactly(3))->method('trans')->willReturnMap([
            ['paSupportEmail', [], 'common', null, 'pa-email@publicguardian.gov.uk'],
            ['helpline', [], 'common', null, '07777777777'],
            ['activation.fromName', [], 'email', null, 'OPG']
        ]);

        $paDeputy = $this->generateUser()->setRoleName(User::ROLE_PA_ADMIN);

        $email = $this->generateSUT()->createActivationEmail($paDeputy);

        $this->assertSame('pa-email@publicguardian.gov.uk', $email->getParameters()['email'] ?? null);
        $this->assertSame('07777777777', $email->getParameters()['phone'] ?? null);
    }

    public function testCreateInvitationEmailLayUser(): void
    {
        $this->router->expects($this->once())->method('generate')->with('user_activate', [
            'action' => 'activate',
            'token' => 'regToken',
        ])->willReturn('/activate/regToken');

        $this->translator->expects($this->exactly(3))->method('trans')->willReturnMap([
            ['layDeputySupportEmail', [], 'common', null, 'help-email@publicguardian.gov.uk'],
            ['helpline', [], 'common', null, '0123456789'],
            ['activation.fromName', [], 'email', null, 'OPG']
        ]);

        $expectedTemplateParams = array_merge($this->getContactParameters(), [
            'link' => 'https://front.base.url/activate/regToken',
            'deputyName' => 'Buford Mcfarling',
        ]);

        $email = $this->generateSUT()->createInvitationEmail($this->layDeputy, 'Buford Mcfarling');

        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame('OPG', $email->getFromName());
        $this->assertSame('user@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::INVITATION_LAY_TEMPLATE_ID, $email->getTemplate());
        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testCreateInvitationEmailOrgUser(): void
    {
        $profDeputy = $this->generateUser()
            ->setEmail('l.wolny@somesolicitors.org')
            ->setRoleName('ROLE_PROF_TEAM_MEMBER');

        $this->router->expects($this->once())->method('generate')->with('user_activate', [
            'action' => 'activate',
            'token' => 'regToken',
        ])->willReturn('/activate/regToken');

        $this->translator->expects($this->exactly(3))->method('trans')->willReturnMap([
            ['profSupportEmail', [], 'common', null, 'prof-email@publicguardian.gov.uk'],
            ['helpline', [], 'common', null, '07987654321'],
            ['activation.fromName', [], 'email', null, 'OPG']
        ]);

        $expectedTemplateParams = [
            'email' => 'prof-email@publicguardian.gov.uk',
            'phone' => '07987654321',
            'link' => 'https://front.base.url/activate/regToken',
        ];

        $email = $this->generateSUT()->createInvitationEmail($profDeputy);

        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame('OPG', $email->getFromName());
        $this->assertSame('l.wolny@somesolicitors.org', $email->getToEmail());
        $this->assertSame(MailFactory::INVITATION_ORG_TEMPLATE_ID, $email->getTemplate());
        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public static function getLayReportTypes(): array
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
     * @dataProvider getLayReportTypes
     */
    public function testCreateReportSubmissionConfirmationEmailForLayDeputy(string $reportType): void
    {
        $this->router->method('generate')->with('homepage', [])->willReturn('');

        $this->translator->expects($this->once())->method('trans')->willReturnMap([
            ['reportSubmissionConfirmation.fromName', [], 'email', null, 'OPG']
        ]);

        $this->submittedReport->setType($reportType);
        $email = $this->generateSUT()->createReportSubmissionConfirmationEmail($this->layDeputy, $this->submittedReport, $this->newReport);

        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame(MailFactory::REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID, $email->getTemplate());
        $this->assertSame('OPG', $email->getFromName());
        $this->assertSame('user@digital.justice.gov.uk', $email->getToEmail());

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
            'PFA' => str_starts_with($reportType, '104') ? 'no' : 'yes',
            'lay' => 'yes',
        ];

        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public static function getOrgReportTypes(): array
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
     * @dataProvider getOrgReportTypes
     */
    public function testCreateReportSubmissionConfirmationEmailForOrgDeputy($reportType, $role)
    {
        $clientFullName = $this->client->getFullname();
        $caseNumber = $this->client->getCaseNumber();

        $this->router->method('generate')->with('homepage', [])->willReturn('');

        $this->translator->expects($this->exactly(2))->method('trans')->willReturnMap([
            ['reportSubmissionConfirmation.fromName', [], 'email', null, 'OPG'],
            ['caseDetails', ['%fullClientName%' => $clientFullName, '%caseNumber%' => $caseNumber], 'email-report-submission-confirm', null, 'Client: Joanne Bloggs Case number: 12345678']
        ]);

        $this->submittedReport->setType($reportType);
        $deputy = $this->generateUser($role);
        $email = $this->generateSUT()->createReportSubmissionConfirmationEmail($deputy, $this->submittedReport, $this->newReport);

        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame(MailFactory::REPORT_SUBMITTED_CONFIRMATION_TEMPLATE_ID, $email->getTemplate());
        $this->assertSame('OPG', $email->getFromName());
        $this->assertSame('user@digital.justice.gov.uk', $email->getToEmail());

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
            'PFA' => str_starts_with($reportType, '104') ? 'no' : 'yes',
            'lay' => 'no',
        ];

        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testCreateResetPasswordEmail(): void
    {
        $this->router->expects($this->exactly(2))->method('generate')->willReturnMap([
            ['user_activate', ['action' => 'password-reset', 'token' => 'regToken'], UrlGeneratorInterface::ABSOLUTE_PATH, '/reset-password/regToken'],
            ['password_forgotten', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/password-managing/forgotten']
        ]);

        $this->translator->expects($this->exactly(3))->method('trans')->willReturnMap([
            ['layDeputySupportEmail', [], 'common', null, 'help-email@publicguardian.gov.uk'],
            ['helpline', [], 'common', null, '0123456789'],
            ['resetPassword.fromName', [], 'email', null, 'OPG']
        ]);

        $expectedTemplateParams = array_merge($this->getContactParameters(), [
            'resetLink' => 'https://front.base.url/reset-password/regToken',
            'recreateLink' => 'https://front.base.url/password-managing/forgotten',
        ]);

        $email = $this->generateSUT()->createResetPasswordEmail($this->layDeputy);

        $this->assertSame(MailFactory::NOTIFY_FROM_EMAIL_ID, $email->getFromEmailNotifyID());
        $this->assertSame('OPG', $email->getFromName());
        $this->assertSame('user@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::RESET_PASSWORD_TEMPLATE_ID, $email->getTemplate());
        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testCreateUpdateClientDetailsEmail(): void
    {
        $this->translator->expects($this->exactly(2))->method('trans')->willReturnMap([
            ['client.fromName', [], 'email', null, 'OPG'],
            ['client.subject', [], 'email', null, 'A subject'],
        ]);

        $client = $this->generateClient();

        $email = $this->generateSUT()->createUpdateClientDetailsEmail($client);

        $this->assertStaticEmailProperties($email);

        $this->assertSame('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::CLIENT_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

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

        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testCreateUpdateClientDetailsEmailCountryNotSet()
    {
        $this->translator->expects($this->exactly(2))->method('trans')->willReturnMap([
            ['client.fromName', [], 'email', null, 'OPG'],
            ['client.subject', [], 'email', null, 'A subject'],
        ]);

        $client = $this->generateClient()->setCountry(null);

        $email = $this->generateSUT()->createUpdateClientDetailsEmail($client);

        $this->assertStaticEmailProperties($email);

        $this->assertSame('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::CLIENT_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

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

        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testCreateUpdateDeputyDetailsEmail()
    {
        $this->translator->expects($this->exactly(2))->method('trans')->willReturnMap([
            ['client.fromName', [], 'email', null, 'OPG'],
            ['client.subject', [], 'email', null, 'A subject'],
        ]);

        $email = $this->generateSUT()->createUpdateDeputyDetailsEmail($this->layDeputy);

        $this->assertStaticEmailProperties($email);

        $this->assertSame('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::DEPUTY_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

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
            'email' => 'user@digital.justice.gov.uk',
        ];

        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }

    public function testCreateUpdateDeputyDetailsEmailCountryNotSet()
    {
        $this->translator->expects($this->exactly(2))->method('trans')->willReturnMap([
            ['client.fromName', [], 'email', null, 'OPG'],
            ['client.subject', [], 'email', null, 'A subject'],
        ]);

        $email = $this->generateSUT()->createUpdateDeputyDetailsEmail($this->layDeputy->setAddressCountry(null));

        $this->assertStaticEmailProperties($email);

        $this->assertSame('updateAddress@digital.justice.gov.uk', $email->getToEmail());
        $this->assertSame(MailFactory::DEPUTY_DETAILS_CHANGE_TEMPLATE_ID, $email->getTemplate());

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
            'email' => 'user@digital.justice.gov.uk',
        ];

        $this->assertSame($expectedTemplateParams, $email->getParameters());
    }
}
