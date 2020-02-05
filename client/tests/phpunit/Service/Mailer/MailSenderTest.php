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

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ValidatorInterface
     */
    private $mockeryValidator;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|LoggerInterface
     */
    private $mockeryLogger;

    /**
     * @var Email|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $mockeryEmail;

    /**
     * @var NotifyClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $mockeryNotifyClient;

    /**
     * @var ObjectProphecy&ValidatorInterface
     */
    private $validator;

    /**
     * @var ObjectProphecy&LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectProphecy&NotifyClient
     */
    private $notifyClient;

    public function setup(): void
    {
        // TODO remove and switch to Prophecy when re-writing existing tests
        $this->mockeryValidator = m::mock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $this->mockeryLogger = m::mock('Psr\Log\LoggerInterface');
        $this->mockeryEmail = m::mock('AppBundle\Model\Email');
        $this->mockeryNotifyClient = m::mock('Alphagov\Notifications\Client');

        $this->mailSender = new MailSender($this->mockeryValidator, $this->mockeryLogger, $this->mockeryNotifyClient);

        $this->validator = self::prophesize(ValidatorInterface::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->notifyClient = self::prophesize(NotifyClient::class);

        $this->sut = new MailSender($this->validator->reveal(), $this->logger->reveal(), $this->notifyClient->reveal());
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testSendValidateErrors()
    {
        $this->mockeryEmail = m::stub('AppBundle\Model\Email', [
            'getParameters' => null
        ]);

        $violations = m::mock('Symfony\Component\Validator\ConstraintViolationList', [
                'count' => 1,
                '__toString' => 'violationsAsString',
        ]);
        $this->mockeryValidator->shouldReceive('validate')->once()->with($this->mockeryEmail, null, ['text'])->andReturn($violations);

        try {
            $this->mailSender->send($this->mockeryEmail, ['text']);
            $this->fail('exception not thrown as expected');
        } catch (\RuntimeException $e) {
            $this->assertEquals('violationsAsString', $e->getMessage());
        }
    }

    public function testSendValidateMissingTransport()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->mockeryValidator->shouldReceive('validate')->andReturn([]);
        $this->mockeryEmail = m::stub('AppBundle\Model\Email', [
            'getParameters' => null
        ]);

        $this->mailSender->send($this->mockeryEmail, ['text']);
    }

    public function testSendOk()
    {
        $transportMock = new Transport\TransportMock();
        $mockMailer = new \Swift_Mailer($transportMock);
        $this->mailSender->addSwiftMailer('default', $mockMailer);

        $this->mockeryEmail = m::stub('AppBundle\Model\Email', [
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
            'getParameters' => null
        ]);

        $this->mockeryValidator->shouldReceive('validate')->andReturn([]);
        $this->mockeryLogger->shouldReceive('log')->with('info', m::any(), m::any());

        $ret = $this->mailSender->send($this->mockeryEmail, ['text']);
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
    public function send_notify()
    {
        $email = $this->generateEmail();
        $this->notifyClient->sendEmail('to@email.address', '123-template-id', ['param' => 'param value'], '', 'fake-id')->shouldBeCalled();
        self::assertTrue($this->sut->send($email));
    }

    /**
     * @test
     * @group acs
     */
    public function send_notify_exceptions_are_logged()
    {
        $email = $this->generateEmail();
        $this->logger->error('Error message')->shouldBeCalled();
        $this->notifyClient->sendEmail(Argument::cetera())->willThrow(new NotifyException('Error message'));

        self::assertFalse($this->sut->send($email));
    }

    /**
     * @return Email
     */
    private function generateEmail(
        string $toEmail='to@email.address',
        string $templateID='123-template-id',
        array $parameters=['param' => 'param value'],
        string $fromEmailNotifyID='fake-id'
    )
    {
        return (new Email())
            ->setToEmail($toEmail)
            ->setTemplate($templateID)
            ->setParameters($parameters)
            ->setFromEmailNotifyID($fromEmailNotifyID);
    }
}
