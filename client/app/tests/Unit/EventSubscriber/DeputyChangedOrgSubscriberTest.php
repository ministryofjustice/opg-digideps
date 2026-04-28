<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputyChangedOrgEvent;
use OPG\Digideps\Frontend\EventSubscriber\DeputyChangedOrgSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\DeputyHelper;
use OPG\Digideps\Frontend\TestHelpers\OrganisationHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class DeputyChangedOrgSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [
                DeputyChangedOrgEvent::NAME => 'auditLog',
            ],
            DeputyChangedOrgSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function auditLog()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new \DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);

        $trigger = 'DEPUTY_CHANGED_ORG';

        //      Client record currently in database
        $client = ClientHelpers::createClient();
        $clientOrg = OrganisationHelpers::createActivatedOrganisation();
        $deputy = DeputyHelper::createDeputy();
        $client->setDeputy($deputy);
        $client->setOrganisation($clientOrg);

        $deputyId = $client->getDeputy()->getId();
        $previousOrgId = $client->getOrganisation()->getId();

        //      New organisation linked to client
        $newOrg = OrganisationHelpers::createActivatedOrganisation();
        $client->setOrganisation($newOrg);
        $newOrgId = $client->getOrganisation()->getId();
        $clientId = $client->getId();

        $sut = new DeputyChangedOrgSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $deputyChangedOrgEvent = new DeputyChangedOrgEvent($trigger, $deputyId, $previousOrgId, $newOrgId, $clientId);

        $expectedEvent = [
            'trigger' => $trigger,
            'date_deputy_changed' => $now->format(\DateTime::ATOM),
            'deputy_id' => $deputyId,
            'organisation_moved_from' => $previousOrgId,
            'organisation_moved_to' => $newOrgId,
            'clients_moved_over' => $clientId,
            'event' => AuditEvents::EVENT_DEPUTY_CHANGED_ORG,
            'type' => 'audit',
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->auditLog($deputyChangedOrgEvent);
    }
}
