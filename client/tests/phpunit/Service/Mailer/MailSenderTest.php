<?php declare(strict_types=1);

namespace AppBundle\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use AppBundle\Model\Email;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

//class MailSenderTest extends \PHPUnit_Framework_TestCase
class MailSenderTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
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
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->notifyClient = self::prophesize(NotifyClient::class);

        $this->sut = new MailSender($this->logger->reveal(), $this->notifyClient->reveal());
    }

    /**
     * @test
     */
    public function send_notify()
    {
        $email = $this->generateEmail();
        $this->notifyClient->sendEmail('to@email.address', '123-template-id', ['param' => 'param value'], '', 'fake-id')->shouldBeCalled();
        self::assertTrue($this->sut->send($email));
    }

    /**
     * @test
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
