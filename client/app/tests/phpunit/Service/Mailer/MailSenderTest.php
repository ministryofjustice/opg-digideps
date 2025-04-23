<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use App\Model\Email;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\UserHelpers;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MailSenderTest extends WebTestCase
{
    use ProphecyTrait;

    private ObjectProphecy|LoggerInterface $logger;
    private ObjectProphecy|NotifyClient $notifyClient;
    private ObjectProphecy|DateTimeProvider $dateTimeProvider;
    private ObjectProphecy|TokenStorageInterface $tokenStorage;
    private MailSender $sut;

    public function setup(): void
    {
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->notifyClient = self::prophesize(NotifyClient::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);

        $user = UserHelpers::createSuperAdminUser();
        $token = new UsernamePasswordToken($user, 'firewall', $user->getRoles());
        $this->tokenStorage->getToken()->willReturn($token);

        $this->dateTimeProvider->getDateTime()->willReturn(new \DateTime());

        $this->sut = new MailSender(
            $this->logger->reveal(),
            $this->notifyClient->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->tokenStorage->reveal()
        );
    }

    /**
     * @test
     */
    public function sendNotify()
    {
        $email = $this->generateEmail();

        $this->logger->notice(Argument::cetera())->shouldBeCalled();
        $this->notifyClient->sendEmail('to@email.address', MailFactory::ACTIVATION_TEMPLATE_ID, ['param' => 'param value'], '', 'fake-id')->shouldBeCalled();

        self::assertTrue($this->sut->send($email));
    }

    /**
     * @return Email
     */
    private function generateEmail(
        string $toEmail = 'to@email.address',
        string $templateID = MailFactory::ACTIVATION_TEMPLATE_ID,
        array $parameters = ['param' => 'param value'],
        string $fromEmailNotifyID = 'fake-id'
    ) {
        return (new Email())
            ->setToEmail($toEmail)
            ->setTemplate($templateID)
            ->setParameters($parameters)
            ->setFromEmailNotifyID($fromEmailNotifyID);
    }

    /**
     * @test
     */
    public function sendNotifyExceptionsAreLogged()
    {
        $email = $this->generateEmail();

        $this->logger->notice(Argument::cetera())->shouldBeCalled();
        $this->logger->error('Error sending email: Error message')->shouldBeCalled();

        $this->notifyClient->sendEmail(Argument::cetera())->willThrow(new NotifyException('Error message'));

        self::assertFalse($this->sut->send($email));
    }
}
