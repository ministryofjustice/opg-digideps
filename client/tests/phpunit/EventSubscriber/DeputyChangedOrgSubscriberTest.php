<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\DeputyChangedOrgEvent;
use App\Entity\Client;
use App\EventSubscriber\DeputyChangedOrgSubscriber;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\ClientHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
//use DateTime;
use Symfony\Component\Validator\Constraints\Date;

class DeputyChangedOrgSubscriberTest extends TestCase
{
    private ObjectProphecy $dateTimeProvider;
    private ObjectProphecy $logger;

//    public function setUp(): void
//    {
//        $this->logger = self::prophesize(LoggerInterface::class);
//        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
//
//        $this->sut = new DeputyChangedOrgSubscriber(
//            $this->logger->reveal(),
//            $this->dateTimeProvider->reveal(),
//        );
//    }

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ DeputyChangedOrgEvent::NAME => 'auditLog'],
            DeputyChangedOrgSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function auditLog()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $trigger = 'DEPUTY_CHANGED_ORG';

        $sut = new DeputyChangedOrgSubscriber($logger->willreveal(), $dateTimeProvider->reveal());

        $client = ClientHelpers::createClient();
        $previousDeputyOrg = $client->getOrganisation();

        $event = new DeputyChangedOrgEvent($trigger, $previousDeputyOrg, $client);

        $expectedEvent = [
            'trigger' => $trigger,
            'date_deputy_changed' => $now->format(DateTime::ATOM),
            'deputy_id' => $client->getNamedDeputy()->getId(),
            'organisation_moved_from' => $previousDeputyOrg,
            'organisation_moved_to' => $client->getOrganisation(),
            'clients_moved_over' => $client->getId(),
        ];

        $this->logger->notice($expectedLogMessage, $expectedEvent)->shouldBeCalled();
        $this->sut->auditLog($event);

    }

}

