<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserAddedToOrganisationEvent;
use OPG\Digideps\Frontend\Event\UserRemovedFromOrganisationEvent;
use OPG\Digideps\Frontend\EventSubscriber\OrgUserMembershipSubscriber;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\OrganisationHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OrgUserMembershipSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                UserAddedToOrganisationEvent::NAME => 'logUserAddedEvent',
                UserRemovedFromOrganisationEvent::NAME => 'logUserRemovedEvent',
            ],
            OrgUserMembershipSubscriber::getSubscribedEvents()
        );
    }

    public function testLogUserAddedEvent(): void
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $addedUser = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';
        $expectedEventName = 'USER_ADDED_TO_ORG';

        $dateTimeProvider = self::createMock(DateTimeProvider::class);
        $now = new \DateTime();
        $dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);

        $expectedAuditEvent = [
            'trigger' => $trigger,
            'added_user_email' => $addedUser->getEmail(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'added_on' => $now->format(\DateTime::ATOM),
            'added_by' => $currentUser->getEmail(),
            'event' => $expectedEventName,
            'type' => 'audit',
        ];

        $logger = self::createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('notice')->with('', $expectedAuditEvent);

        $userAddedToOrganisationEvent = new UserAddedToOrganisationEvent($organisation, $addedUser, $currentUser, $trigger);

        $sut = new OrgUserMembershipSubscriber($logger, $dateTimeProvider);
        $sut->logUserAddedEvent($userAddedToOrganisationEvent);
    }

    public function testLogUserRemovedEvent(): void
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToRemove = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';
        $expectedEventName = 'USER_REMOVED_FROM_ORG';

        $dateTimeProvider = self::createMock(DateTimeProvider::class);
        $now = new \DateTime();
        $dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);

        $expectedAuditEvent = [
            'trigger' => $trigger,
            'removed_user_email' => $userToRemove->getEmail(),
            'removed_user_name' => $userToRemove->getFullName(),
            'organisation_identifier' => $organisation->getEmailIdentifier(),
            'organisation_id' => $organisation->getId(),
            'removed_on' => $now->format(\DateTime::ATOM),
            'removed_by' => $currentUser->getEmail(),
            'event' => $expectedEventName,
            'type' => 'audit',
        ];

        $logger = self::createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('notice')->with('', $expectedAuditEvent);

        $userAddedToOrganisationEvent = new UserRemovedFromOrganisationEvent($organisation, $userToRemove, $currentUser, $trigger);

        new OrgUserMembershipSubscriber($logger, $dateTimeProvider)->logUserRemovedEvent($userAddedToOrganisationEvent);
    }
}
