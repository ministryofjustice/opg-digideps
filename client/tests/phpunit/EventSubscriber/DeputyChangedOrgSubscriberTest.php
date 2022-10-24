<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\DeputyChangedOrgEvent;
use App\EventSubscriber\DeputyChangedOrgSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\NamedDeputyHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use DateTime;
use Symfony\Component\DependencyInjection\Loader\Configurator\Traits\PropertyTrait;

class DeputyChangedOrgSubscriberTest extends TestCase
{
    use PropertyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [
                DeputyChangedOrgEvent::NAME => 'auditLog'
            ],
            DeputyChangedOrgSubscriber::getSubscribedEvents());
    }

    /** @test */
    public function auditLog()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $trigger = 'DEPUTY_CHANGED_ORG';

        $sut = new DeputyChangedOrgSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $namedDeputy = NamedDeputyHelper::createNamedDeputy();

        $clientPreCSVUpload =
            [
                'id' => 30,
                'case_number' => '10000000',
                'court_date' => '2022-01-01',
                'email' => 'client@hotmail.co.uk',
                'first_name' => 'Julie',
                'last_name' => 'Brown',
                'organisation_id' => 7,
                'named_deputy_id' => $namedDeputy->getId(),
             ];

        $previousOrg = $clientPreCSVUpload['organisation_id'];

        $clientPostCSVUpload =
            [
                'id' => 30,
                'case_number' => '10000000',
                'court_date' => '2022-01-01',
                'email' => 'client@hotmail.co.uk',
                'first_name' => 'Julie',
                'last_name' => 'Brown',
                'organisation_id' => 8,
                'named_deputy_id' => $namedDeputy->getId()
            ];

        $newOrg = $clientPostCSVUpload['organisation_id'];

        $deputyChangedEvent = new DeputyChangedOrgEvent($trigger, $previousOrg, $newOrg, $clientPostCSVUpload);

        $expectedEvent = [
            'trigger' => $trigger,
            'date_deputy_changed' => $now->format(DateTime::ATOM),
            'deputy_id' => $clientPreCSVUpload['named_deputy_id'],
            'organisation_moved_from' => $previousOrg,
            'organisation_moved_to' => $newOrg,
            'clients_moved_over' => $clientPreCSVUpload['id'],
            'event' => AuditEvents::EVENT_DEPUTY_CHANGED_ORG,
            'type' => 'audit',
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->auditLog($deputyChangedEvent);
    }
}

