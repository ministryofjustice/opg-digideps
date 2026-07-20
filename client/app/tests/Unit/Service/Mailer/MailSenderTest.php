<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use OPG\Digideps\Frontend\Model\Email;
use OPG\Digideps\Frontend\Service\Mailer\MailFactory;
use OPG\Digideps\Frontend\Service\Mailer\MailSender;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MailSenderTest extends WebTestCase
{
    private MockObject&LoggerInterface $logger;
    private MockObject&NotifyClient $notifyClient;
    private MockObject&DateTimeProvider $dateTimeProvider;
    private MockObject&TokenStorageInterface $tokenStorage;
    private MailSender $sut;

    public function setup(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->notifyClient = $this->createMock(NotifyClient::class);
        $this->dateTimeProvider = $this->createMock(DateTimeProvider::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $user = UserHelpers::createSuperAdminUser();
        $token = new UsernamePasswordToken($user, 'firewall', $user->getRoles());
        $this->tokenStorage->method('getToken')->willReturn($token);

        $this->dateTimeProvider->method('getDateTime')->willReturn(new \DateTime());

        $this->sut = new MailSender(
            $this->logger,
            $this->notifyClient,
            $this->dateTimeProvider,
            $this->tokenStorage
        );
    }

    public function testSendNotify(): void
    {
        $email = $this->generateEmail();

        $this->logger->expects($this->atLeastOnce())->method('notice');
        $this->notifyClient->expects($this->atLeastOnce())->method('sendEmail')->with('to@email.address', MailFactory::ACTIVATION_TEMPLATE_ID, ['param' => 'param value'], '', 'fake-id');

        $this->assertTrue($this->sut->send($email));
    }

    private function generateEmail(
        string $toEmail = 'to@email.address',
        string $templateID = MailFactory::ACTIVATION_TEMPLATE_ID,
        array $parameters = ['param' => 'param value'],
        string $fromEmailNotifyID = 'fake-id'
    ): Email {
        return new Email()
            ->setToEmail($toEmail)
            ->setTemplate($templateID)
            ->setParameters($parameters)
            ->setFromEmailNotifyID($fromEmailNotifyID);
    }

    public function testSendNotifyExceptionsAreLogged(): void
    {
        $email = $this->generateEmail();

        $this->logger->expects($this->atLeastOnce())->method('notice');
        $this->logger->expects($this->atLeastOnce())->method('error')->with('Error sending email: Error message');

        $this->notifyClient->method('sendEmail')->willThrowException(new NotifyException('Error message'));

        $this->assertFalse($this->sut->send($email));
    }
}
