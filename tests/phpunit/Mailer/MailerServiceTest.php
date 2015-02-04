<?php
namespace AppBundle\Mailer;

use Symfony\Component\Templating\EngineInterface;

use \Mockery as m;

class MailerServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailerService 
     */
    protected $mailerService;

    
    protected function setUp()
    {
        $this->transport = m::mock('Swift_Transport'); 
        
        $this->mailerService = new MailerService($this->transport); 
    }

    /**
     * Set the sender details
     * 
     * @param string $email
     * @param string $name
     */
//    public function testSetFrom()
//    {
//        $this->mailerService->setFrom('foo@bar.com', 'Foo Bar');
//
//        $message = m::mock('\Swift_Mime_Message');
//        $message->shouldReceive('setBody')->once();
//        $message->shouldReceive('addPart')->once()->with(m::any(), 'text/html');
//        $message->shouldReceive('setFrom')->once()->with('foo@bar.com', 'Foo Bar');
//        $message->shouldReceive('setSubject');
//        
//        $this->transport->shouldReceive('isStarted');
//        $this->transport->shouldReceive('start');
//        $this->transport->shouldReceive('send')->once();
//
//        $this->view->shouldReceive('render')->once()->with(array(1=>2, 3=>4));
//        $this->view->shouldReceive('renderHtml')->once()->with(array(1=>2, 3=>4));
//        $this->view->shouldReceive('getSubject');
//                 
//        $this->mailerService->sendHtml($message, $this->view, array(1=>2, 3=>4));
//    }
//
    public function testSend()
    {
        $message = m::mock('\Swift_Mime_Message');
        $message->shouldReceive('setBody')->once();
        $message->shouldReceive('addPart')->once()->with(m::any(), 'text/html');
//        $message->shouldReceive('setFrom')->once();
        $message->shouldReceive('setSubject');
        
        $this->transport->shouldReceive('isStarted');
        $this->transport->shouldReceive('start');
        $this->transport->shouldReceive('send')->once();

//        $this->view->shouldReceive('render')->once()->with(array(1=>2, 3=>4));
//        $this->view->shouldReceive('renderHtml')->once()->with(array(1=>2, 3=>4));
//        $this->view->shouldReceive('getSubject');
                 
        $this->mailerService->sendMimeMessage($message, 's', 'body', 'bodyHtml');
    }
//
//    public function testSendText()
//    {
//        $message = m::mock('\Swift_Mime_Message');
//        $message->shouldReceive('setBody')->once();
//        $message->shouldReceive('setFrom')->once();
//        $message->shouldReceive('setSubject')->once();
//
//        $this->transport->shouldReceive('isStarted')->once();
//        $this->transport->shouldReceive('start')->once();
//        $this->transport->shouldReceive('send')->once();
//
//        $this->view->shouldReceive('render')->once()->with([]);
//        $this->view->shouldReceive('getSubject')->once();
//
//        $this->mailerService->addFilter($filter);
//        $this->mailerService->sendText($message, $this->view, []);
//    }
//    
    public function teardown()
    {
        m::close();
    }
    
}
