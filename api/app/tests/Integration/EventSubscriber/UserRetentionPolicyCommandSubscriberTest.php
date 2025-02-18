<?php

declare(strict_types=1);

namespace App\Tests\Integration\EventSubscriber;

use App\Entity\User;
use App\Event\UserRetentionPolicyCommandEvent;
use App\EventSubscriber\UserRetentionPolicyCommandSubscriber;
use App\Service\Audit\AuditEvents;
use App\TestHelpers\UserTestHelper;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRetentionPolicyCommandSubscriberTest extends KernelTestCase
{
    use ProphecyTrait;

    private ObjectProphecy $logger;

    private \App\Service\Time\DateTimeProvider $dateTimeProvider;

    private UserRetentionPolicyCommandSubscriber $sut;

    public function setUp(): void
    {
        $this->dateTimeProvider = new \App\Service\Time\DateTimeProvider();
    }

    /**
     * @test
     */
    public function getSubscribedEvents()
    {
        self::assertEquals([
            UserRetentionPolicyCommandEvent::NAME => 'logEvent',
        ], UserRetentionPolicyCommandSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function logEvent()
    {
        $this->logger = self::prophesize(LoggerInterface::class);

        $this->sut = (new UserRetentionPolicyCommandSubscriber(
            $this->logger->reveal(),
            $this->dateTimeProvider
        ));

        $trigger = 'A_TRIGGER';
        $user = new UserTestHelper();
        $deletedAdminUser = $user->createUser(null, User::ROLE_ADMIN_MANAGER);
        $deletedAdminUser->setId(1);
        $deletedAdminUser->setLastLoggedIn(new \DateTime('-25 months'));

        $now = new \DateTime();

        $expectedEvent = [
            'trigger' => $trigger,
            'id' => $deletedAdminUser->getId(),
            'email_address' => $deletedAdminUser->getEmail(),
            'message' => 'Deletion due to two year retention policy',
            'deleted_on' => $now->format(\DateTime::ATOM),
            'event' => AuditEvents::USER_DELETED_AUTOMATION,
            'type' => 'audit',
        ];

        $this->logger->notice('', $expectedEvent)->shouldBeCalled();

        $userRetentionDeletionEvent = new UserRetentionPolicyCommandEvent($deletedAdminUser, $trigger);

        $this->sut->logEvent($userRetentionDeletionEvent);
    }
}
