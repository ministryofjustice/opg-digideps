<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Event\ClientUpdatedEvent;
use AppBundle\Event\UserDeletedEvent;
use AppBundle\EventSubscriber\ClientUpdatedSubscriber;
use AppBundle\EventSubscriber\UserDeletedSubscriber;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\ClientHelpers;
use AppBundle\TestHelpers\UserHelpers;
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
