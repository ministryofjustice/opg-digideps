<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\AdminManagerCreatedEvent;
use OPG\Digideps\Frontend\Event\AdminManagerDeletedEvent;
use OPG\Digideps\Frontend\Event\AdminUserCreatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\AdminUserLifeCycleSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AdminUserLifeCycleSubscriberTest extends TestCase
{
    private UserHelpers $userHelpers;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private LoggerInterface&MockObject$logger;
    private Mailer&MockObject $mailer;
    private AdminUserLifeCycleSubscriber $sut;

    public function setUp(): void
    {
        $this->userHelpers = new UserHelpers();
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->mailer = self::createMock(Mailer::class);

        $this->sut = new AdminUserLifeCycleSubscriber(
            $this->mailer,
            $this->logger,
            $this->dateTimeProvider
        );
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertEquals([
            AdminUserCreatedEvent::NAME => 'sendEmail',
            AdminManagerCreatedEvent::NAME => 'logAdminManagerCreatedEvent',
            AdminManagerDeletedEvent::NAME => 'logAdminManagerDeletedEvent',
        ], AdminUserLifeCycleSubscriber::getSubscribedEvents());
    }

    public function testSendEmail(): void
    {
        $createdUser = $this->userHelpers->createUser();
        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);

        $this->mailer->expects(self::once())
            ->method('sendActivationEmail')
            ->with($createdUser);

        $this->sut->sendEmail($userCreatedEvent);
    }

    public function testLogAdminManagerCreatedEvent(): void
    {
        $now = new \DateTime('now');

        $currentUser = $this->userHelpers->createSuperAdminUser();
        $createdAdminManager = $this->userHelpers->createAdminManager();
        $trigger = 'ADMIN_MANAGER_MANUALLY_CREATED';

        $expectedEvent = [
            'trigger' => $trigger,
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_user_last_name' => $currentUser->getLastname(),
            'logged_in_user_email' => $currentUser->getEmail(),
            'admin_user_first_name' => $createdAdminManager->getFirstname(),
            'admin_user_last_name' => $createdAdminManager->getLastname(),
            'admin_user_email' => $createdAdminManager->getEmail(),
            'created_on' => $now->format(\DateTime::ATOM),
            'event' => AuditEvents::EVENT_ADMIN_MANAGER_CREATED,
            'type' => 'audit',
        ];

        $this->dateTimeProvider->expects(self::once())
            ->method('getDateTime')
            ->willReturn($now);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with('', $expectedEvent);

        $adminManagerCreatedEvent = new AdminManagerCreatedEvent($trigger, $currentUser, $createdAdminManager);

        $this->sut->logAdminManagerCreatedEvent($adminManagerCreatedEvent);
    }

    public function testLogAdminManagerDeletedEvent(): void
    {
        $now = new \DateTime('now');

        $currentUser = $this->userHelpers->createSuperAdminUser();
        $deletedAdminManager = $this->userHelpers->createAdminManager();
        $trigger = 'ADMIN_MANAGER_MANUALLY_DELETED';

        $expectedEvent = [
            'trigger' => $trigger,
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_user_last_name' => $currentUser->getLastname(),
            'logged_in_user_email' => $currentUser->getEmail(),
            'admin_user_first_name' => $deletedAdminManager->getFirstname(),
            'admin_user_last_name' => $deletedAdminManager->getLastname(),
            'admin_user_email' => $deletedAdminManager->getEmail(),
            'created_on' => $now->format(\DateTime::ATOM),
            'event' => AuditEvents::EVENT_ADMIN_MANAGER_DELETED,
            'type' => 'audit',
        ];

        $this->dateTimeProvider->expects(self::once())
            ->method('getDateTime')
            ->willReturn($now);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with('', $expectedEvent);

        $adminManagerDeletedEvent = new AdminManagerDeletedEvent($trigger, $currentUser, $deletedAdminManager);

        $this->sut->logAdminManagerDeletedEvent($adminManagerDeletedEvent);
    }
}
