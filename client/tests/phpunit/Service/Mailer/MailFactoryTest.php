<?php

namespace AppBundle\Service\Mailer;

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
    private $baseURLs;

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

    public function setUp(): void
    {
//        $this->router = m::mock('Symfony\Component\Routing\Router');
//        $this->translator = m::mock('Symfony\Component\Translation\DataCollectorTranslator');
//        $this->templating = m::mock('Symfony\Bundle\TwigBundle\TwigEngine')->makePartial();
//        $this->translator->shouldReceive('trans')->andReturnUsing(function ($input) {
//            return $input . ' translated';
//        });
//
//        $this->container = m::mock('Symfony\Component\DependencyInjection\Container');
//        $this->container->shouldReceive('get')->with('translator')->andReturn($this->translator);
//        $this->container->shouldReceive('get')->with('templating')->andReturn($this->templating);
//        $this->container->shouldReceive('get')->with('router')->andReturn($this->router);
//        $this->container->shouldReceive('getParameter')->with('non_admin_host')->andReturn('http://deputy/');
//        $this->container->shouldReceive('getParameter')->with('admin_host')->andReturn('http://admin/');
//        $this->container->shouldReceive('getParameter')->with('email_send')->andReturn([
//            'from_email' => 'from@email',
//        ]);
//        $this->container->shouldReceive('getParameter')->with('email_report_submit')->andReturn([
//            'from_email' => 'ers_from@email',
//            'to_email' => 'ers_to@email',
//        ]);
//
//        $this->user = m::mock('AppBundle\Entity\User', [
//            'isDeputy' => true,
//            'getFullName' => 'FN',
//            'getRegistrationToken' => 'RT',
//            'getEmail' => 'user@email',
//        ])->makePartial();
//
//        $this->paUser = m::mock('AppBundle\Entity\User', [
//            'isDeputyPa' => true,
//            'isDeputyOrg' => true,
//            'getFullName' => 'FN',
//            'getRegistrationToken' => 'RT',
//            'getEmail' => 'pauser@email',
//        ])->makePartial();
//
//        $this->object = new MailFactory($this->container);

        $this->layDeputy = (new User())
            ->setRegistrationToken('regToken')
            ->setEmail('user@digital.justice.gov.uk')
            ->setFirstname('Joe')
            ->setLastname('Bloggs')
            ->setRoleName(User::ROLE_LAY_DEPUTY);

        $this->baseURLs = [
            'front' => 'https://front.base.url',
            'admin' => 'https://admin.base.url'
        ];

        $this->emailSendParams = [
            'from_email' => 'from@digital.justice.gov.uk'
        ];

        $this->translator = self::prophesize('Symfony\Bundle\FrameworkBundle\Translation\Translator');
        $this->router = self::prophesize('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->templating = self::prophesize('Symfony\Bundle\TwigBundle\TwigEngine');
    }

    public function testcreateActivationEmail()
    {
        $this->router->shouldReceive('generate')->with('homepage', [])->andReturn('homepage');
        $this->router->shouldReceive('generate')->with('user_activate', ['action' => 'activate', 'token' => 'RT'])->andReturn('ua');

        $this->templating->shouldReceive('render')->with(
            'AppBundle:Email:user-activate.html.twig',
            m::any()
        )->andReturn('template.html');

        $this->templating->shouldReceive('render')->with(
            'AppBundle:Email:user-activate.text.twig',
            m::any()
        )->andReturn('template.text');

        $email = $this->object->createActivationEmail($this->user);

        $this->assertEquals('template.html', $email->getBodyHtml());
        $this->assertEquals('template.text', $email->getBodyText());
        $this->assertEquals('user@email', $email->getToEmail());
        $this->assertEquals('from@email', $email->getFromEmail());
    }

    public function testcreateOrgReportSubmissionConfirmationEmail()
    {
        $this->router->shouldReceive('generate')->withAnyArgs()->andReturn('https://mock.com');

        $this->templating->shouldReceive('render')->withAnyArgs()->andReturn('[TEMPLATE]');

        $client = m::mock('AppBundle\Entity\Client', [
            'getCaseNumber' => '1234567t',
            'getFullname' => 'FN'
        ]);
        $report = m::mock('AppBundle\Entity\Report\Report', [
            'getClient' => $client,
            'getEndDate' => new \DateTime('2016-12-31'),
            'getSubmitDate' => new \DateTime('2017-01-01'),
        ]);
        $newReport = m::mock('AppBundle\Entity\Report\Report', [
            'getClient' => $client,
            'getType' => '102',
            'getEndDate' => new \DateTime('2017-12-31'),
            'getSubmitDate' => new \DateTime('2018-01-01'),
        ]);
        $email = $this->object->createOrgReportSubmissionConfirmationEmail($this->paUser, $report, $newReport);

        $this->assertEquals('[TEMPLATE]', $email->getBodyHtml());
        $this->assertEquals('pauser@email', $email->getToEmail());
        $this->assertEmpty($email->getAttachments());
    }

    /**
     * @test
     */
    public function createResetPasswordEmail()
    {
//        non_admin_host
//        admin_host
//        ('email_send')['from_email']

        $this->router->generate('user_activate', [
            'action' => 'password-reset',
            'token'  => 'regToken'
        ])->shouldBeCalled()->willReturn('/reset-password/regToken');

        $this->translator->trans('resetPassword.fromName', [], 'email')->shouldBeCalled()->willReturn('OPG');
        $this->translator->trans('resetPassword.subject', [], 'email')->shouldBeCalled()->willReturn('Reset Password Subject');

        $sut = new MailFactory(
            $this->translator->reveal(),
            $this->router->reveal(),
            $this->templating->reveal(),
            $this->baseURLs
        );

        $email = $sut->createResetPasswordEmail($this->layDeputy, $this->emailSendParams);

        self::assertStringContainsString('from@digital.justice.gov.uk', $email->getFromEmail());
        self::assertStringContainsString('OPG', $email->getFromName());
        self::assertStringContainsString('user@digital.justice.gov.uk', $email->getToEmail());
        self::assertStringContainsString('Joe Bloggs', $email->getToName());
        self::assertStringContainsString('Reset Password Subject', $email->getSubject());
        self::assertStringContainsString(MailFactory::RESET_PASSWORD_TEMPLATE, $email->getTemplate());

        $expectedTemplateParams = ['resetLink' => 'https://front.base.url/reset-password/regToken'];
        self::assertEquals($expectedTemplateParams, $email->getParameters());

    }
}
