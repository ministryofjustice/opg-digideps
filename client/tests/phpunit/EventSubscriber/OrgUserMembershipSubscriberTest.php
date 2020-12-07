<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserAddedToOrganisationEvent;
use AppBundle\Event\UserRemovedFromOrganisationEvent;
use AppBundle\EventSubscriber\OrgUserMembershipSubscriber;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\OrganisationHelpers;
use AppBundle\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OrgUserMembershipSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [
                UserAddedToOrganisationEvent::NAME => 'logUserAddedEvent',
                UserRemovedFromOrganisationEvent::NAME => 'logUserRemovedEvent'
            ],
            OrgUserMembershipSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function logUserAddedEvent()
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $addedUser = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';
        $expectedEventName = 'USER_ADDED_TO_ORG';

        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);

        $expectedAuditEvent = [
            'trigger' => $trigger,
            'added_user_email' => $addedUser->getEmail(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'added_on' => $now->format(DateTime::ATOM),
            'added_by' => $currentUser->getEmail(),
            'event' => $expectedEventName,
            'type' => 'audit'
        ];

        $logger = self::prophesize(LoggerInterface::class);
        $logger
            ->notice('', $expectedAuditEvent)
            ->shouldBeCalled();

        $userAddedToOrganisationEvent = new UserAddedToOrganisationEvent($organisation, $addedUser, $currentUser, $trigger);

        $sut = new OrgUserMembershipSubscriber($logger->reveal(), $dateTimeProvider->reveal());
        $sut->logUserAddedEvent($userAddedToOrganisationEvent);
    }

    /** @test */
    public function logUserRemovedEvent()
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToRemove = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';
        $expectedEventName = 'USER_REMOVED_FROM_ORG';

        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);

        $expectedAuditEvent = [
            'trigger' => $trigger,
            'removed_user_email' => $userToRemove->getEmail(),
            'removed_user_name' => $userToRemove->getFullName(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'removed_on' => $now->format(DateTime::ATOM),
            'removed_by' => $currentUser->getEmail(),
            'event' => $expectedEventName,
            'type' => 'audit'
        ];

        $logger = self::prophesize(LoggerInterface::class);
        $logger
            ->notice('', $expectedAuditEvent)
            ->shouldBeCalled();

        $userAddedToOrganisationEvent = new UserRemovedFromOrganisationEvent($organisation, $userToRemove, $currentUser, $trigger);

        $sut = new OrgUserMembershipSubscriber($logger->reveal(), $dateTimeProvider->reveal());
        $sut->logUserRemovedEvent($userAddedToOrganisationEvent);
    }
}
