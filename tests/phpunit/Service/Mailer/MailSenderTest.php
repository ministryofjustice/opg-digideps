<?php

namespace AppBundle\Service\Mailer;

use MockeryStub as m;

//class MailSenderTest extends \PHPUnit_Framework_TestCase
class MailSenderTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    /**
     * @var MailSender
     */
    private $mailSender;

    public function setup()
    {
        $this->validator = m::mock('Symfony\Component\Validator\ValidatorInterface');
        $this->logger = m::mock('Psr\Log\LoggerInterface');
        $this->email = m::mock('AppBundle\Model\Email');
        $this->redis = m::mock('Predis\Client');

        $this->mailSender = new MailSender($this->validator, $this->logger, $this->redis);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testSendValidateErrors()
    {
        $violations = m::mock('Symfony\Component\Validator\ConstraintViolationList', [
                'count' => 1,
                '__toString' => 'violationsAsString',
        ]);
        $this->validator->shouldReceive('validate')->once()->with($this->email, ['text'])->andReturn($violations);

        try {
            $this->mailSender->send($this->email, ['text']);
            $this->fail('exception not thrown as expected');
        } catch (\RuntimeException $e) {
            $this->assertEquals('violationsAsString', $e->getMessage());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSendValidateMissingTransport()
    {
        $this->validator->shouldReceive('validate')->andReturn([]);

        $this->mailSender->send($this->email, ['text'], 'default');
    }

    public function testSendOk()
    {
        $transportMock = new Transport\TransportMock();
        $mockMailer = new \Swift_Mailer($transportMock);
        $this->mailSender->addSwiftMailer('default', $mockMailer);

        $this->email = m::stub('AppBundle\Model\Email', [
            'getToEmail' => 't@example.org',
            'getToName' => 'tn',
            'getFromEmail' => 'f@example.org',
            'getFromName' => 'fn',
            'getSubject' => 's',
            'getBodyText' => 'bt',
            'getBodyHtml' => 'bh',
            'getAttachments' => [
                m::stub('AppBundle\Model\EmailAttachment', [
                    'getContent' => 'c',
                    'getFilename' => 'f',
                    'getContentType' => 'application/octect',
                ]),
            ],
        ]);

        $this->validator->shouldReceive('validate')->andReturn([]);
        $this->logger->shouldReceive('log')->with('info', m::any(), m::any());

        $ret = $this->mailSender->send($this->email, ['text'], 'default');
        $this->assertEquals(['result' => 'sent'], $ret);

        // assert sent message
        $this->assertCount(1, $transportMock->getSentMessages());
        $message = $transportMock->getSentMessages()[0]; /* @var $message \Swift_Message */
        $this->assertEquals(['t@example.org' => 'tn'], $message->getTo());
        $this->assertEquals(['f@example.org' => 'fn'], $message->getFrom());
        $this->assertEquals('s', $message->getSubject());
        $this->assertEquals('bt', $message->getBody());

        $this->assertInstanceOf('Swift_Mime_MimePart', $message->getChildren()[0]);
        $this->assertEquals('bh', $message->getChildren()[0]->getBody());
        $this->assertEquals('text/html', $message->getChildren()[0]->getContentType());

        $this->assertInstanceOf('Swift_Attachment', $message->getChildren()[1]);
        $this->assertEquals('c', $message->getChildren()[1]->getBody());
        $this->assertEquals('f', $message->getChildren()[1]->getFilename());
        $this->assertEquals('application/octect', $message->getChildren()[1]->getContentType());
    }

    public function testSendRedisMock()
    {
        $this->markTestSkipped('');
        $transportMock = new Transport\TransportMock();
        $mockMailer = new \Swift_Mailer($transportMock);
        $this->mailSender->addSwiftMailer('default', $mockMailer);

        $this->email = m::stub('AppBundle\Model\Email', [
            'getToEmail' => 'behat-t@example.org',
            'getToName' => 'tn',
            'getFromEmail' => 'f@example.org',
            'getFromName' => 'fn',
            'getSubject' => 's',
            'getBodyText' => 'bt',
            'getBodyHtml' => 'bh',
            'getAttachments' => [
                m::stub('AppBundle\Model\EmailAttachment', [
                    'getContent' => 'c',
                    'getFilename' => 'f',
                    'getContentType' => 'application/octect',
                ]),
            ],
        ]);

        $this->validator->shouldReceive('validate')->andReturn([]);

        $this->mailSender->send($this->email, ['text'], 'default');

        $this->assertEquals(['behat-t@example.org' => 'tn'], $emailJson['to']);

        // assert sent message
        $this->assertCount(0, $transportMock->getSentMessages());
    }
}
