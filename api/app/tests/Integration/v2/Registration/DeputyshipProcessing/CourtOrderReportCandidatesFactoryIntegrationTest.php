<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\StagingDeputyship;
use App\Tests\Integration\ApiBaseTestCase;
use App\v2\Registration\DeputyshipProcessing\CourtOrderReportCandidatesFactory;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

final class CourtOrderReportCandidatesFactoryIntegrationTest extends ApiBaseTestCase
{
    private CourtOrderReportCandidatesFactory $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CourtOrderReportCandidatesFactory $sut */
        $sut = $this->container->get(CourtOrderReportCandidatesFactory::class);
        $this->sut = $sut;
    }

    // create a report which is not compatible with a deputyship (CSV row) due to type differences
    private function createIncompatiblyTypedReport(Client $client, string $orderType): Report
    {
        // a 104 is not compatible with a hybrid or pfa deputyship
        $incompatibleReportType = '104';
        if ('hw' === $orderType) {
            // a 102 is not compatible with a hybrid or hw deputyship
            $incompatibleReportType = '102';
        }

        return new Report(
            client: $client,
            type: $incompatibleReportType,
            startDate: new DateTime(),
            endDate: new DateTime(),
            dateChecks: false
        );
    }

    // create a report which is not compatible with a deputyship due to starting too early
    private function createIncompatiblyDatedReport(Client $client, string $orderType, DateTime $madeDate): Report
    {
        // make sure types are compatible
        $compatibleReportType = '102';
        if ('hw' === $orderType) {
            $compatibleReportType = '104';
        }

        // report starts a year before the made date of the court order, so is not compatible for that reason
        $oneYearAgo = $madeDate->modify('-1 year');

        return new Report(
            client: $client,
            type: $compatibleReportType,
            startDate: $oneYearAgo,
            endDate: $oneYearAgo,
            dateChecks: false
        );
    }

    public static function compatibleReportDataProvider(): array
    {
        return [
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => null, 'compatibleReportType' => '102'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => null, 'compatibleReportType' => '103'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => null, 'compatibleReportType' => '104'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '102-4'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '103-4'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '102-4'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '103-4'],

            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => null, 'compatibleReportType' => '102-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => null, 'compatibleReportType' => '103-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => null, 'compatibleReportType' => '104-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '102-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '103-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '102-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '103-4-6'],

            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => null, 'compatibleReportType' => '102-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => null, 'compatibleReportType' => '103-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => null, 'compatibleReportType' => '104-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '102-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '103-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '102-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '103-4-5'],
        ];
    }

    #[DataProvider('compatibleReportDataProvider')]
    public function testCreateCompatibleReportCandidates(
        string $deputyType,
        string $orderType,
        ?string $isHybrid,
        string $compatibleReportType,
    ): void {
        $deputyUid = '12121212';
        $caseNumber = '12345678';
        $orderUid = '66667777';
        $madeDate = new DateTime();

        // add staging deputyship
        $deputyship = new StagingDeputyship();
        $deputyship->orderUid = $orderUid;
        $deputyship->deputyUid = $deputyUid;
        $deputyship->deputyType = $deputyType;
        $deputyship->orderType = $orderType;
        $deputyship->isHybrid = $isHybrid;
        $deputyship->caseNumber = $caseNumber;
        $deputyship->orderMadeDate = $madeDate->format('Y-m-d');

        $this->entityManager->persist($deputyship);
        $this->entityManager->flush();

        // add client
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        // add compatible report
        $report1 = new Report(
            client: $client,
            type: $compatibleReportType,
            startDate: $madeDate,
            endDate: $madeDate,
            dateChecks: false
        );

        $this->entityManager->persist($report1);
        $this->entityManager->flush();

        // add an incompatibly typed report (just to make sure we don't pick it up as compatible)
        $report2 = $this->createIncompatiblyTypedReport($client, $orderType);

        // add an incompatibly dated report (again, to make sure it's not picked up as a candidate)
        $report3 = $this->createIncompatiblyDatedReport($client, $orderType, $madeDate);

        $this->entityManager->persist($report2);
        $this->entityManager->persist($report3);
        $this->entityManager->flush();

        // create compatible report candidates
        $candidates = iterator_to_array($this->sut->createCompatibleReportCandidates());

        // assertions
        self::assertCount(1, $candidates);
        self::assertEquals(DeputyshipCandidateAction::InsertOrderReport, $candidates[0]->action);
        self::assertEquals($orderUid, $candidates[0]->orderUid);
        self::assertEquals($report1->getId(), $candidates[0]->reportId);
    }

    public function testCreateCompatibleNdrCandidates(): void
    {
        $caseNumber = '77677775';
        $orderUid = '88884444';
        $madeDate = new DateTime();

        // add pfa/LAY staging deputyship
        $deputyship = new StagingDeputyship();
        $deputyship->deputyUid = '11112234';
        $deputyship->orderUid = $orderUid;
        $deputyship->deputyType = 'LAY';
        $deputyship->orderType = 'pfa';
        $deputyship->caseNumber = $caseNumber;
        $deputyship->orderMadeDate = $madeDate->format('Y-m-d');

        $this->entityManager->persist($deputyship);
        $this->entityManager->flush();

        // add client
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        // add NDR to client
        $ndr = new Ndr($client);
        $ndr->setStartDate($madeDate);

        $this->entityManager->persist($ndr);
        $this->entityManager->flush();

        // create NDR candidates
        $candidates = iterator_to_array($this->sut->createCompatibleNdrCandidates());

        // assertions
        self::assertCount(1, $candidates);
        self::assertEquals(DeputyshipCandidateAction::InsertOrderNdr, $candidates[0]->action);
        self::assertEquals($orderUid, $candidates[0]->orderUid);
        self::assertEquals($ndr->getId(), $candidates[0]->ndrId);
    }

    // if a court_order_report row exists for a court order <-> report relationship, it should
    // not be selected as a candidate (see DDLS-797)
    public function testCreateCompatibleReportsDoesNotSuggestAlreadyRelated(): void
    {
        $orderMadeDate = new DateTime();

        // add pfa/LAY staging deputyship referencing an existing court order record
        $deputyship = new StagingDeputyship();
        $deputyship->deputyUid = '11112234';
        $deputyship->orderUid = '9888777666';
        $deputyship->deputyType = 'LAY';
        $deputyship->orderType = 'pfa';
        $deputyship->caseNumber = '9988776655';
        $deputyship->orderMadeDate = $orderMadeDate->format('Y-m-d');
        $deputyship->isHybrid = '0';

        $this->entityManager->persist($deputyship);

        // add client
        $client = new Client();
        $client->setCaseNumber($deputyship->caseNumber);

        $this->entityManager->persist($client);

        // add compatible report
        $report = new Report(
            client: $client,
            type: '102',
            startDate: $orderMadeDate->modify('+1 day'),
            endDate: $orderMadeDate->modify('+1 year'),
            dateChecks: false
        );

        $this->entityManager->persist($report);

        // create order and associate with report; this report is a potential candidate,
        // but should be ignored as a candidate because a relationship already exists
        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid($deputyship->orderUid);
        $courtOrder->setOrderType('pfa');
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate($orderMadeDate);
        $courtOrder->addReport($report);

        $this->entityManager->persist($courtOrder);

        // write everything to db
        $this->entityManager->flush();

        // create report candidates
        $candidates = iterator_to_array($this->sut->createCompatibleReportCandidates());

        // check that the existing order <-> report relationship is not one of the candidates
        self::assertCount(0, $candidates);
    }
}
