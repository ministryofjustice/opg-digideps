<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\UserAddedToOrganisationEvent;
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
            [UserAddedToOrganisationEvent::NAME => 'logEvent'],
            OrgUserMembershipSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function logEvent()
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
        $sut->logEvent($userAddedToOrganisationEvent);
    }
}
