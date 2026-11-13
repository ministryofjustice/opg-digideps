<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Mail\EmailTemplate;
use OPG\Digideps\Common\Mail\NotifyMail;
use OPG\Digideps\Frontend\Service\Mailer\MailFactory;
use OPG\Digideps\Frontend\Service\Mailer\MailSender;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Uid\Uuid;

class MailSenderTest extends WebTestCase
{
    private MockObject&LoggerInterface $logger;
    private MockObject&NotifyClient $notifyClient;
    private MailSender $sut;
    private Uuid $fakeNotifyId;

    public function setup(): void
    {
        $this->fakeNotifyId = Uuid::v4();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->notifyClient = $this->createMock(NotifyClient::class);
        $dateTimeProvider = $this->createMock(DateTimeProvider::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $user = UserHelpers::createSuperAdminUser();
        $token = new UsernamePasswordToken($user, 'firewall', $user->getRoles());
        $tokenStorage->method('getToken')->willReturn($token);

        $dateTimeProvider->method('getDateTime')->willReturn(new \DateTime());

        $this->sut = new MailSender(
            $this->logger,
            $this->notifyClient,
            $dateTimeProvider,
            $tokenStorage
        );
    }

    public function testSendNotify(): void
    {
        $email = $this->generateEmail();

        $this->logger->expects(self::atLeastOnce())->method('notice');

        $this->notifyClient->expects(self::atLeastOnce())
            ->method('sendEmail')
            ->with('to@email.address', MailFactory::ACTIVATION_TEMPLATE_ID, ['param' => 'param value'], '', $this->fakeNotifyId->toRfc4122());

        self::assertTrue($this->sut->send($email));
    }

    private function generateEmail(): NotifyMail
    {
        return new NotifyMail(
            new Email('to@email.address'),
            EmailTemplate::Activation,
            ['param' => 'param value'],
            $this->fakeNotifyId
        );
    }

    public function testSendNotifyExceptionsAreLogged(): void
    {
        $email = $this->generateEmail();

        $this->logger->expects($this->atLeastOnce())->method('notice');
        $this->logger->expects($this->atLeastOnce())->method('error')->with('Error sending email: Error message');

        $this->notifyClient->method('sendEmail')->willThrowException(new NotifyException('Error message'));

        self::assertFalse($this->sut->send($email));
    }
}
