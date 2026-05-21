<?php

namespace Tests\OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Staging\StagingSelectedCandidate;
use OPG\Digideps\Backend\Factory\UpdateReportTypeDataFactory;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;

class ReportTypeUpdateDataFactoryIntegrationTest extends ApiIntegrationTestCase
{
    private static UpdateReportTypeDataFactory $sut;
    private static Fixtures $fixtures;

    private static int $count = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var UpdateReportTypeDataFactory $sut */
        $sut = self::$container->get(UpdateReportTypeDataFactory::class);
        self::$sut = $sut;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        self::purgeDatabase();
    }

    public static function reportTypeChanges(): array
    {
        return [
            'Lay to Pro - nonLayDeputyAdded' => [[
                'orderType' => CourtOrderType::PFA,
                'orderKind' => CourtOrderKind::Single,
                'reportType' => CourtOrderReportType::OPG103,
                'action' => DeputyshipCandidateAction::InsertOrderDeputy,
                'deputyType' => DeputyType::PRO,
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::PROF_PFA_LOW_ASSETS_TYPE,
                'updatedCount' => 1,
                'errorCount' => 0,
            ]],
            'PFA-low to PFA-high - baseReportChange' =>  [[
                'orderType' => CourtOrderType::PFA,
                'orderKind' => CourtOrderKind::Single,
                'reportType' => CourtOrderReportType::OPG102,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'deputyType' => DeputyType::LAY,
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
                'updatedCount' => 1,
                'errorCount' => 0,
            ]],
            'PFA to HW - baseReportChange' =>  [[
                'orderType' => CourtOrderType::HW,
                'orderKind' => CourtOrderKind::Single,
                'reportType' => CourtOrderReportType::OPG104,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'deputyType' => DeputyType::LAY,
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_HW_TYPE,
                'updatedCount' => 1,
                'errorCount' => 0,
            ]],
            'PFA to Hybrid - changedToHybrid' =>  [[
                'orderType' => CourtOrderType::HW,
                'orderKind' => CourtOrderKind::Hybrid,
                'reportType' => CourtOrderReportType::OPG102,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'deputyType' => DeputyType::LAY,
                'existingReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
                'updatedCount' => 0,
                'errorCount' => 0,
            ]],
            'Hybrid to PFA - changedFromHybrid' =>  [[
                'orderType' => CourtOrderType::PFA,
                'orderKind' => CourtOrderKind::Dual,
                'reportType' => CourtOrderReportType::OPG102,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'deputyType' => DeputyType::LAY,
                'existingReportType' => Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
                'updatedCount' => 0,
                'errorCount' => 0,
            ]],
            'No-change' => [[
                'orderType' => CourtOrderType::PFA,
                'orderKind' => CourtOrderKind::Single,
                'reportType' => CourtOrderReportType::OPG103,
                'action' => DeputyshipCandidateAction::UpdateDeputyStatus,
                'deputyType' => DeputyType::LAY,
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'updatedCount' => 0,
                'errorCount' => 0,
            ]],
        ];
    }

    private function setUpTestData(array $data): Report
    {
        ++self::$count;

        $uid = '' . self::$count;

        /** @var CourtOrderType $courtOrderType */
        $courtOrderType = $data['orderType'];

        /** @var CourtOrderReportType $courtOrderReportType */
        $courtOrderReportType = $data['reportType'];

        /** @var CourtOrderKind $courtOrderKind */
        $courtOrderKind = $data['orderKind'];

        /** @var DeputyshipCandidateAction $action */
        $action = $data['action'];

        $courtOrder = self::$fixtures->createCourtOrder(
            uid: $uid,
            type: $courtOrderType,
            kind: $courtOrderKind,
            status: 'ACTIVE',
            courtOrderReportType: $courtOrderReportType,
        );

        $deputy = self::$fixtures->createDeputy([
            'setDeputyUid' => $uid,
            'setDeputyType' => $data['deputyType'],
        ]);

        $client = self::$fixtures->createClient();

        $report = self::$fixtures->createReport($client, ['setType' => $data['existingReportType']]);

        $candidate = new StagingSelectedCandidate($action, $uid);

        self::$fixtures->persist($courtOrder, $deputy, $client, $report, $candidate);

        $courtOrder->addReport($report);
        $deputy->associateWithCourtOrder($courtOrder);

        self::$fixtures->flush();
        self::$fixtures->refresh($report);
        self::$fixtures->refresh($courtOrder);

        return $report;
    }

    #[DataProvider('reportTypeChanges')]
    public function testProcessCandidates(array $data): void
    {
        $report = $this->setUpTestData($data);

        $dataFactoryResult = self::$sut->run(false);

        /** @var string $expectedReportType */
        $expectedReportType = $data['expectedReportType'];

        /** @var string $updatedCount */
        $updatedCount = $data['updatedCount'];

        /** @var int $errorCount */
        $errorCount = $data['errorCount'];

        $this->assertEquals(
            $data['expectedReportType'],
            $report->getType(),
            sprintf(
                'ReportType differs from expected type "%s"',
                $expectedReportType,
            )
        );

        $this->assertStringContainsString(
            "Updated $updatedCount report types",
            $dataFactoryResult->getMessages()['success'][0],
            'Number of report types updated did not match expectation'
        );
        $this->assertCount(
            $errorCount,
            $dataFactoryResult->getErrorMessages()['errors'],
            'Expected error count did not match actual error count'
        );
    }

    #[DataProvider('reportTypeChanges')]
    public function testProcessCandidatesDryRun(array $data): void
    {
        $report = $this->setUpTestData($data);

        $dataFactoryResult = self::$sut->run(true);

        /** @var string $expectedReportType */
        $expectedReportType = $data['existingReportType'];

        /** @var string $existingReportType */
        $existingReportType = $data['existingReportType'];

        $this->assertEquals(
            $expectedReportType,
            $report->getType(),
            sprintf(
                'ReportType differs from expected type "%s"',
                $existingReportType,
            )
        );

        $this->assertStringContainsString('0', $dataFactoryResult->getMessages()['success'][0], 'Updated String Count');
    }
}
