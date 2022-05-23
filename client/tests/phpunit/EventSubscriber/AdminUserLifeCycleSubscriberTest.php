<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\AdminManagerCreatedEvent;
use App\Event\AdminUserCreatedEvent;
use App\EventSubscriber\AdminUserLifeCycleSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\UserHelpers;
use DateTime;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AdminUserLifeCycleSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @var UserHelpers */
    private $userHelpers;

    /** @var ObjectProphecy */
    private $dateTimeProvider;

    /** @var ObjectProphecy */
    private $logger;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var AdminUserLifeCycleSubscriber */
    private $sut;

    public function setUp(): void
    {
        $this->userHelpers = new UserHelpers();
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->logger = self::prophesize(Logger::class);
        $this->mailer = self::prophesize(Mailer::class);

        $this->sut = (new AdminUserLifeCycleSubscriber(
            $this->mailer->reveal(),
            $this->logger->reveal(),
            $this->dateTimeProvider->reveal()
        ));
    }

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals([
            AdminUserCreatedEvent::NAME => 'sendEmail',
            AdminManagerCreatedEvent::NAME => 'auditLog',
        ], AdminUserLifeCycleSubscriber::getSubscribedEvents());
    }

    /** @test */
    public function sendEmail()
    {
        $createdUser = $this->userHelpers->createUser();
        $userCreatedEvent = new AdminUserCreatedEvent($createdUser);

        $this->mailer->sendActivationEmail($createdUser)->shouldBeCalled();

        $this->sut->sendEmail($userCreatedEvent);
    }

    /** @test */
    public function auditLog()
    {
        $now = new DateTime('now');

        $currentUser = $this->userHelpers->createSuperAdminUser();
        $createdAdminManager = $this->userHelpers->createAdminManager();
        $trigger = 'ADMIN_MANUAL_ORG_CREATION';

        $expectedEvent = [
            'trigger' => $trigger,
            'logged_in_user_first_name' => $currentUser->getFirstname(),
            'logged_in_last_name' => $currentUser->getLastname(),
            'admin_user_first_name' => $createdAdminManager->getFirstname(),
            'admin_user_last_name' => $createdAdminManager->getLastname(),
            'admin_user_email' => $createdAdminManager->getEmail(),
            'created_on' => $now->format(DateTime::ATOM),
            'event' => AuditEvents::TRIGGER_ADMIN_MANAGER_MANUALLY_CREATED,
            'type' => 'audit',
        ];

        $this->dateTimeProvider->getDateTime()->willReturn($now);
        $this->logger->notice('', $expectedEvent)->shouldBeCalled();

        $adminManagerCreatedEvent = new AdminManagerCreatedEvent($trigger, $currentUser, $createdAdminManager);

        $this->sut->auditLog($adminManagerCreatedEvent);
    }
}
