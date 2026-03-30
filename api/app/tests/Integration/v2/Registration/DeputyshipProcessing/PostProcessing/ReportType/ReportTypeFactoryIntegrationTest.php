<?php

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\Entity\Report\Report;
use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Factory\DataFactoryResult;
use App\Repository\ReportRepository;
use App\Service\ReportTypeService;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\Tests\Integration\Fixtures;
use App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType\ReportTypeBuilderResult;
use App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType\ReportTypeUpdateFactory;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

class ReportTypeFactoryIntegrationTest extends ApiIntegrationTestCase
{
    private static ReportTypeUpdateFactory $sut;
    private static Fixtures $fixtures;

    private int $count = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var ReportTypeUpdateFactory $sut */
        $sut = self::$container->get(ReportTypeUpdateFactory::class);
        self::$sut = $sut;
    }

    public static function reportTypeChanges(): array
    {
        return [
            'Lay to Pro - nonLayDeputyAdded' => [[
                'orderUid' => 1111111,
                'action' => DeputyshipCandidateAction::InsertOrderDeputy,
                'orderType' => 'pfa',
                'reportType' => 'opg103',
                'deputyType' => 'PRO',
                'isHybrid' => '0',
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::PROF_PFA_LOW_ASSETS_TYPE
            ]],
            'PFA-low to PFA-high - baseReportChange' =>  [[
                'orderUid' => 1111113,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'orderType' => 'pfa',
                'reportType' => 'opg103',
                'deputyType' => 'LAY',
                'isHybrid' => '0',
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE
            ]],
            'PFA to HW - baseReportChange' =>  [[
                'orderUid' => 1111114,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'orderType' => 'hw',
                'reportType' => 'opg104',
                'deputyType' => 'LAY',
                'isHybrid' => '0',
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_HW_TYPE
            ]],
            'PFA to Hybrid - changedToHybrid' =>  [[
                'orderUid' => 1111116,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'orderType' => 'hw',
                'reportType' => 'opg102',
                'deputyType' => 'LAY',
                'isHybrid' => '1',
                'existingReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_COMBINED_HIGH_ASSETS_TYPE
            ]],
            'Hybrid to PFA - changedFromHybrid' =>  [[
                'orderUid' => 1111118,
                'action' => DeputyshipCandidateAction::InsertOrderReport,
                'orderType' => 'pfa',
                'reportType' => 'opg102',
                'deputyType' => 'LAY',
                'isHybrid' => '0',
                'existingReportType' => Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_HIGH_ASSETS_TYPE
            ]],
            'No-change' => [[
                'orderUid' => 2222221,
                'action' => DeputyshipCandidateAction::UpdateDeputyStatus,
                'orderType' => 'pfa',
                'reportType' => 'opg103',
                'deputyType' => 'LAY',
                'isHybrid' => '0',
                'existingReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE,
                'expectedReportType' => Report::LAY_PFA_LOW_ASSETS_TYPE
            ]],
        ];
    }

    /**
     * @dataProvider reportTypeChanges
     */
    public function testProcessCandidates(array $data)
    {
        $sql = <<<SQL
        SELECT count(1)
        FROM staging.selectedcandidates
        SQL;

        self::$entityManager->getConnection()->executeQuery($sql);

        ++$this->count;

        $existingReportAttributes = ReportTypeService::getReportTypeAttributes($data['existingReportType']);

        $orderUid = $data['orderUid'];

        $courtOrder = self::$fixtures->createCourtOrder(
            $orderUid,
            $existingReportAttributes['orderType'],
            'ACTIVE'
        );
        $client = self::$fixtures->createClient();
        $report = self::$fixtures->createReport($client, ['setType' => $data['existingReportType']]);

        $courtOrder->addReport($report);

        self::$fixtures->persist($courtOrder, $client, $report);
        self::$fixtures->flush();

        $deputyship = new StagingDeputyship();
        $deputyship->orderUid = $orderUid;
        $deputyship->deputyUid = $this->count;
        $deputyship->isHybrid = $data['isHybrid'];

        $candidate = new StagingSelectedCandidate($data['action'], $orderUid);
        $candidate->deputyType = $data['deputyType'];
        $candidate->reportType = $data['reportType'];
        $candidate->deputyUid = $this->count;
        $candidate->orderType = $data['orderType'];

        self::$fixtures->persist($candidate, $deputyship);
        self::$fixtures->flush();

        /** @var ?DataFactoryResult $dataFactoryResult */
        $dataFactoryResult = null;
        /** @var ?ReportTypeBuilderResult $results */
        $results = null;

        [$dataFactoryResult, $results] = self::$sut->run();

        error_log('report ID - ' . $report->getId());
        $updateReport = self::$entityManager
            ->getRepository(Report::class)
            ->findOneBy(['id' => $report->getId()]);
        error_log('report Type test Updated object - ' . $updateReport->getType());

        $updatedReport = self::$fixtures->getReportById($report->getId());
        $this->assertEquals(
            $data['expectedReportType'],
            $updatedReport->getType(),
            sprintf(
                'ReportType differs from expected type "%s"',
                $data['expectedReportType'],
            )
        );
    }
}
