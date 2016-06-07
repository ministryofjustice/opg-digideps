<?php

namespace AppBundle\Service\Mailer;

use MockeryStub as m;

class MailFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MailFactory 
     */
    private $object;


    public function setUp()
    {
        $this->translator = m::mock('Symfony\Component\Translation\DataCollectorTranslator');
        $this->templating = m::mock('Symfony\Bundle\TwigBundle\TwigEngine');
        $this->translator->shouldReceive('trans')->andReturnUsing(function($input){
            return $input.' translated';
        });
        
        $this->container = m::mock('Symfony\Component\DependencyInjection\Container');
        $this->container->shouldReceive('get')->with('translator')->andReturn($this->translator);
        $this->container->shouldReceive('get')->with('templating')->andReturn($this->templating);

        $roleToArea = ["ROLE_ADMIN" => "admin", "ROLE_AD" => "admin", "ROLE_LAY_DEPUTY" => "frontend"];
        $this->object = new MailFactory($this->container, $roleToArea);
    }


    public function testcreateReportEmail()
    {
        $this->container->shouldReceive('getParameter')->with('email')->andReturn([
            'base_url'=>['frontend'=>'http://site'],
            'routes' => ['homepage'=>'/']
        ]);
        $this->container->shouldReceive('getParameter')->with('email_report_submit')->andReturn([
            'from_email' => 'from@email',
            'to_email' => 'to@email',
        ]);
        
        $this->templating->shouldReceive('render')->with(
            'AppBundle:Email:report-submission.html.twig', 
            ['homepageUrl' => 'http://site/']
        )->andReturn('[TEMPLATE]');
        
        $user = m::mock('AppBundle\Entity\User', [
            'getRole->getRole'=> 'ROLE_LAY_DEPUTY'
        ]);
        $client = m::mock('AppBundle\Entity\Client', [
            'getCaseNumber'=>'1234567t',
        ]);
        $report = m::mock('AppBundle\Entity\Report', [
            'getClient'=>$client,
            'getEndDate'=>new \DateTime('2016-12-31'),
            'getSubmitDate'=>new \DateTime('2017-01-01'),
        ]);
        $email = $this->object->createReportEmail($user, $report, '[REPORT-CONTENT-PDF]');
        
        $this->assertEquals('[TEMPLATE]', $email->getBodyHtml());
        $this->assertEquals('to@email', $email->getToEmail());
        $this->assertEquals('DigiRep-2016_2017-01-01_1234567t.pdf', $email->getAttachments()[0]->getFilename());
        $this->assertEquals('[REPORT-CONTENT-PDF]', $email->getAttachments()[0]->getContent());
        $this->assertEquals('application/pdf', $email->getAttachments()[0]->getContentType());
    }


    public function testcreateActivationEmail()
    {
        $this->markTestIncomplete(__METHOD__);
    }


    public function testcreateResetPasswordEmail()
    {
        $this->markTestIncomplete(__METHOD__);
    }


    public function testcreateChangePasswordEmail()
    {
        $this->markTestIncomplete(__METHOD__);
    }


    public function testcreateFeedbackEmail()
    {
        $this->markTestIncomplete(__METHOD__);
    }


    public function testcreateReportSubmissionConfirmationEmail()
    {
        $this->markTestIncomplete(__METHOD__);
    }

}