<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Service;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Service\DeputyService;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class DeputyServiceIntegrationTest extends ApiIntegrationTestCase
{
    private static DeputyService $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var DeputyService $sut */
        $sut = self::$container->get(DeputyService::class);
        self::$sut = $sut;
    }

    public function testFindReportsInfoByUidSuccess()
    {
        [
            'client' => $client,
            'persons' => ['deputies' => ['lay1' => $deputy]],
            'orders' => [['pfa' => ['order' => $courtOrder, 'reports' => [$report]]]]
        ] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        $results = self::$sut->findReportsInfoByUid(uid: $deputy->getDeputyUid());

        self::assertCount(1, $results);
        self::assertArrayHasKey('client', $results[0]);

        self::assertArrayHasKey('firstName', $results[0]['client']);
        self::assertEquals($client->getFirstName(), $results[0]['client']['firstName']);

        self::assertArrayHasKey('lastName', $results[0]['client']);
        self::assertEquals($client->getLastName(), $results[0]['client']['lastName']);

        self::assertArrayHasKey('caseNumber', $results[0]['client']);
        self::assertEquals($client->getCaseNumber(), $results[0]['client']['caseNumber']);

        self::assertArrayHasKey('courtOrderUids', $results[0]);
        self::assertEquals([$courtOrder->getCourtOrderUid()], $results[0]['courtOrderUids']);

        self::assertArrayHasKey('courtOrderLink', $results[0]);
        self::assertEquals($courtOrder->getCourtOrderUid(), $results[0]['courtOrderLink']);

        self::assertArrayHasKey('report', $results[0]);
        self::assertArrayHasKey('type', $results[0]['report']);
        self::assertEquals($report->getType(), $results[0]['report']['type']);
    }

    public function testFindReportsInfoByUidDeputyNotActiveOnOrder()
    {
        ['persons' => ['deputies' => ['lay1' => $deputy]],] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay1', isActive: false))
        )));

        $results = self::$sut->findReportsInfoByUid(uid: $deputy->getDeputyUid());

        self::assertEquals(null, $results);
    }

    public function testFindReportsInfoByUidForNonExistentDeputyIsNull()
    {
        $results = self::$sut->findReportsInfoByUid(uid: '70000022');

        self::assertEquals(null, $results);
    }

    public function testFindReportsInfoByUidUsesLatestReportType(): void
    {

        // two reports, one the current report and the other historical;
        // check that the most recent report's type is used as the type for the court order
        // (as displayed on the choose a court order page)
        ['persons' => ['deputies' => ['lay1' => $deputy]]] = self::$fixtureService->instantiateScenario(new Scenario(
            new CourtOrderDescriptor(DeputySet::oneLay(), CourtOrderReportType::OPG102),
            new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), CourtOrderReportType::OPG103, active: false)),
        ));

        $results = self::$sut->findReportsInfoByUid(uid: $deputy->getDeputyUid());

        self::assertCount(1, $results);
        self::assertEquals('102', $results[0]['report']['type']);
    }

    // if there are two court orders for the same report, they display as a single item
    public function testFindReportsInfoByUidCombinesCourtOrders(): void
    {
        // one hybrid report associated with both court orders
        ['persons' => ['deputies' => ['lay1' => $deputy]], 'orders' => [['pfa' => ['order' => $pfa], 'hw' => ['order' => $hw]]]] = self::$fixtureService->instantiateScenario(new Scenario(
            new CourtOrderDescriptor(DeputySet::oneLay(), CourtOrderReportType::OPG102, single: false),
        ));

        $results = self::$sut->findReportsInfoByUid(uid: $deputy->getDeputyUid());

        self::assertCount(1, $results);
        self::assertEquals([$pfa->getCourtOrderUid(), $hw->getCourtOrderUid()], $results[0]['courtOrderUids']);
        self::assertEquals('102-4', $results[0]['report']['type']);
    }
}
