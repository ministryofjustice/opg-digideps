<?php declare(strict_types=1);


namespace Tests\App\EventListener;

use App\Entity\User;
use App\Event\UserDeletedEvent;
use App\EventSubscriber\UserDeletedSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserDeletedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [UserDeletedEvent::NAME => 'logEvent'],
            UserDeletedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider userProvider
     * @test
     */
    public function logEvent(User $deletedUser, string $expectedEventName)
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $sut = new UserDeletedSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $deletedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new UserDeletedEvent($deletedUser, $deletedBy, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'deleted_on' => $now->format(DateTime::ATOM),
            'deleted_by' => $deletedBy->getEmail(),
            'subject_full_name' => $deletedUser->getFullName(),
            'subject_email' => $deletedUser->getEmail(),
            'subject_role' => $deletedUser->getRoleName(),
            'event' => $expectedEventName,
            'type' => 'audit'
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logEvent($event);
    }

    public function userProvider()
    {
        $deletedUser = UserHelpers::createUser();

        return [
            'Admin User' => [(clone $deletedUser)->setRoleName('ROLE_ADMIN'), AuditEvents::EVENT_ADMIN_DELETED],
            'Super Admin User' => [(clone $deletedUser)->setRoleName('ROLE_SUPER_ADMIN'), AuditEvents::EVENT_ADMIN_DELETED],
            'Non-admin user' => [(clone $deletedUser)->setRoleName('ROLE_NOT_AN_ADMIN'), AuditEvents::EVENT_DEPUTY_DELETED],
        ];
    }
}
