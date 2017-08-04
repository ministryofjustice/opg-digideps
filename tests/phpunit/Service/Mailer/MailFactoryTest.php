<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Entity\User;
use MockeryStub as m;

class MailFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailFactory
     */
    private $object;

    public function setUp()
    {
        $this->router = m::mock('Symfony\Component\Routing\Router');
        $this->translator = m::mock('Symfony\Component\Translation\DataCollectorTranslator');
        $this->templating = m::mock('Symfony\Bundle\TwigBundle\TwigEngine')->makePartial();
        $this->translator->shouldReceive('trans')->andReturnUsing(function ($input) {
            return $input . ' translated';
        });

        $this->container = m::mock('Symfony\Component\DependencyInjection\Container');
        $this->container->shouldReceive('get')->with('translator')->andReturn($this->translator);
        $this->container->shouldReceive('get')->with('templating')->andReturn($this->templating);
        $this->container->shouldReceive('get')->with('router')->andReturn($this->router);
        $this->container->shouldReceive('getParameter')->with('non_admin_host')->andReturn('http://deputy/');
        $this->container->shouldReceive('getParameter')->with('admin_host')->andReturn('http://admin/');
        $this->container->shouldReceive('getParameter')->with('email_send')->andReturn([
            'from_email' => 'from@email',
        ]);
        $this->container->shouldReceive('getParameter')->with('email_report_submit')->andReturn([
            'from_email' => 'ers_from@email',
            'to_email' => 'ers_to@email',
        ]);

        $this->user = m::mock('AppBundle\Entity\User', [
            'isDeputy' => true,
            'getFullName' => 'FN',
            'getRegistrationToken' => 'RT',
            'getEmail' => 'user@email',
        ])->makePartial();

        $this->paUser = m::mock('AppBundle\Entity\User', [
            'isDeputyPa' => true,
            'getFullName' => 'FN',
            'getRegistrationToken' => 'RT',
            'getEmail' => 'pauser@email',
        ])->makePartial();

        $this->object = new MailFactory($this->container);
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

    public function testcreatePaReportSubmissionConfirmationEmail()
    {
        $this->router->shouldReceive('generate')->withAnyArgs()->andReturn('https://mock.com');

        $this->templating->shouldReceive('render')->withAnyArgs()->andReturn('[TEMPLATE]');

        $client = m::mock('AppBundle\Entity\Client', [
            'getCaseNumber' => '1234567t',
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
        $email = $this->object->createPaReportSubmissionConfirmationEmail($this->paUser, $report, $newReport, '[REPORT-CONTENT-PDF]');

        $this->assertEquals('[TEMPLATE]', $email->getBodyHtml());
        $this->assertEquals('pauser@email', $email->getToEmail());
        $this->assertEmpty($email->getAttachments());
    }
}
