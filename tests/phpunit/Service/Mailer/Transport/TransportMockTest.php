<?php

namespace AppBundle\Service\Mailer\Transport;

class TransportMockTest extends \PHPUnit_Framework_TestCase
{
    protected $transport;
    protected $mailer;
    protected $path = '/tmp/dd_fwt';

    protected function setUp()
    {
        $this->transport = new FileWriter($this->path);
        file_put_contents($this->path, '');

        $this->mailer = new \Swift_Mailer($this->transport);
    }

    protected function tearDown()
    {
        $this->transport = null;
        $this->mailer = null;
    }

    public function testMailerSavesEMailInsideTheMock()
    {
        $this->mailer->createMessage();

        $message = $this->mailer->createMessage();
        $message->setSubject('test');
        $this->mailer->send($message);

        $this->assertEquals('test', $this->transport->getMessages()[0]->getSubject());
    }

    public function testFindMessage()
    {
        $subject = 'xzy';
        $to = 'test@foobar.com';

        $message = $this->mailer->createMessage();
        $message->setSubject($subject);
        $message->addTo($to);
        $this->mailer->send($message);

        $this->assertNull($this->transport->findMessage('dunno', $to));

        $this->assertEquals($subject, $this->transport->findMessage($subject, $to)->getSubject());
    }

    public function testPersistentStorage()
    {
        $this->mailer->send($this->mailer->createMessage());

        $mock = new FileWriter($this->path);
        $this->assertCount(1, $mock->getMessages());
    }
}
