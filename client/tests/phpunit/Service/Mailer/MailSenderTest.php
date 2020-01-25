<?php declare(strict_types=1);

namespace AppBundle\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use AppBundle\Model\Email;
use MockeryStub as m;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//class MailSenderTest extends \PHPUnit_Framework_TestCase
class MailSenderTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    /**
     * @var MailSender
     */
    private $mailSender;

    public function setup(): void
    {
        $this->validator = m::mock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $this->logger = m::mock('Psr\Log\LoggerInterface');
        $this->email = m::mock('AppBundle\Model\Email');
        $this->notifyClient = m::mock('Alphagov\Notifications\Client');

        $this->mailSender = new MailSender($this->validator, $this->logger, $this->notifyClient);
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testSendValidateErrors()
    {
        $violations = m::mock('Symfony\Component\Validator\ConstraintViolationList', [
                'count' => 1,
                '__toString' => 'violationsAsString',
        ]);
        $this->validator->shouldReceive('validate')->once()->with($this->email, null, ['text'])->andReturn($violations);

        try {
            $this->mailSender->send($this->email, ['text']);
            $this->fail('exception not thrown as expected');
        } catch (\RuntimeException $e) {
            $this->assertEquals('violationsAsString', $e->getMessage());
        }
    }

    public function testSendValidateMissingTransport()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->validator->shouldReceive('validate')->andReturn([]);

        $this->mailSender->send($this->email, ['text']);
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

        $ret = $this->mailSender->send($this->email, ['text']);
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

    /**
     * @test
     * @group acs
     */
    public function sendNotify()
    {
        $email = (new Email())
            ->setToEmail('to@email.address')
            ->setTemplate('123-template-id')
            ->setParameters(['param' => 'param value']);

        $validator = self::prophesize(ValidatorInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        /** @var ObjectProphecy&NotifyClient $notifyClient */
        $notifyClient = self::prophesize(NotifyClient::class);
        $notifyClient->sendEmail('to@email.address', '123-template-id', ['param' => 'param value'])->shouldBeCalled();

        $sut = new MailSender($validator->reveal(), $logger->reveal(), $notifyClient->reveal());
        self::assertTrue($sut->sendNotify($email));
    }

    /**
     * @test
     * @group acs
     */
    public function sendNotify_exception()
    {
        $email = (new Email())
            ->setToEmail('to@email.address')
            ->setTemplate('123-template-id')
            ->setParameters(['param' => 'param value']);

        $validator = self::prophesize(ValidatorInterface::class);
        $logger = self::prophesize(LoggerInterface::class);
        $logger->error('Error message')->shouldBeCalled();

        /** @var ObjectProphecy|NotifyClient $notifyClient */
        $notifyClient = self::prophesize(NotifyClient::class);
        $notifyClient->sendEmail(Argument::any(), Argument::any(), Argument::any())->willThrow(new NotifyException('Error message'));

        $sut = new MailSender($validator->reveal(), $logger->reveal(), $notifyClient->reveal());
        self::assertFalse($sut->sendNotify($email));
    }
}
