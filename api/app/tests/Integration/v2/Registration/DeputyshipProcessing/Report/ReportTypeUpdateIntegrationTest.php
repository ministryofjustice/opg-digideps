<?php

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing\Report\ReportType;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Domain\CourtOrder\CourtOrderReportType;
use App\Domain\CourtOrder\CourtOrderType;
use App\Domain\Deputy\DeputyType;
use App\Entity\Report\Report;
use App\Entity\StagingSelectedCandidate;
use App\Factory\DataFactoryResult;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\Tests\Integration\Fixtures;
use App\v2\Registration\DeputyshipProcessing\Report\ReportTypeUpdate;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

class ReportTypeUpdateIntegrationTest extends ApiIntegrationTestCase
{
    private static ReportTypeUpdate $sut;
    private static Fixtures $fixtures;

    private static int $count = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);
    }

    public function tearDown(): void
    {
        self::$entityManager->flush();
        self::$entityManager->clear();
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
                'expectedReportType' => Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
                'updatedCount' => 1,
                'errorCount' => 1,
            ]],
            'Hybrid to PFA - changedFromHybrid' =>  [[
                'orderType' => CourtOrderType::PFA,
                'orderKind' => CourtOrderKind::Dual,
                'reportType' => CourtOrderReportType::OPG102,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'deputyType' => DeputyType::LAY,
                'existingReportType' => Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
                'updatedCount' => 1,
                'errorCount' => 1,
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

    /**
     * @dataProvider reportTypeChanges
     */
    public function testProcessCandidates(array $data)
    {
        ++self::$count;
        $courtOrder = self::$fixtures->createCourtOrder(
            uid: self::$count,
            type: $data['orderType'],
            kind: $data['orderKind'],
            status: 'ACTIVE',
            courtOrderReportType: $data['reportType'],
        );
        $deputy = self::$fixtures->createDeputy([
            'setDeputyUid' => self::$count,
            'setDeputyType' => $data['deputyType'],
        ]);
        $client = self::$fixtures->createClient();
        $report = self::$fixtures->createReport($client, ['setType' => $data['existingReportType']]);
        $candidate = new StagingSelectedCandidate($data['action'], self::$count);

        self::$fixtures->persist($courtOrder, $deputy, $client, $report, $candidate);

        $courtOrder->addReport($report);
        $deputy->associateWithCourtOrder($courtOrder);

        self::$fixtures->flush();
        self::$fixtures->refresh($report);
        self::$fixtures->refresh($courtOrder);

        /** @var ReportTypeUpdate $sut */
        $sut = self::$container->get(ReportTypeUpdate::class);
        $dataFactoryResult = $sut->run();

        $this->assertEquals(
            $data['expectedReportType'],
            $report->getType(),
            sprintf(
                'ReportType differs from expected type "%s"',
                $data['expectedReportType'],
            )
        );

        $this->assertStringContainsString($data['updatedCount'], $dataFactoryResult->getMessages()['success'][0], 'Updated String Count');
        $this->assertCount($data['errorCount'], $dataFactoryResult->getErrorMessages()['errors'], 'Error count');
    }
}
