<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\v2\Registration\DeputyshipProcessing\CourtOrderReportCandidatesFactory;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CourtOrderReportCandidatesFactoryIntegrationTest extends KernelTestCase
{
    private EntityManager $em;
    private ORMPurger $purger;
    private CourtOrderReportCandidatesFactory $sut;

    protected function setUp(): void
    {
        $container = self::bootKernel()->getContainer();

        $this->em = $container->get('doctrine')->getManager();

        $this->purger = new ORMPurger($this->em);

        /** @var CourtOrderReportCandidatesFactory $sut */
        $sut = $container->get(CourtOrderReportCandidatesFactory::class);
        $this->sut = $sut;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->purger->purge();
    }

    private function compatibleReportsDataProvider(): array
    {
        return [
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '0', 'reportType' => '102'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '0', 'reportType' => '103'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '0', 'reportType' => '104'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '1', 'reportType' => '102-4'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '1', 'reportType' => '103-4'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '1', 'reportType' => '102-4'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '1', 'reportType' => '103-4'],

            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '0', 'reportType' => '102-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '0', 'reportType' => '103-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '0', 'reportType' => '104-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '1', 'reportType' => '102-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '1', 'reportType' => '103-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '1', 'reportType' => '102-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '1', 'reportType' => '103-4-6'],

            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '0', 'reportType' => '102-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '0', 'reportType' => '103-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '0', 'reportType' => '104-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '1', 'reportType' => '102-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '1', 'reportType' => '103-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '1', 'reportType' => '102-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '1', 'reportType' => '103-4-5'],
        ];
    }

    /**
     * @dataProvider compatibleReportsDataProvider
     */
    public function testCreateCompatibleReportCandidates($deputyType, $orderType, $isHybrid, $reportType): void
    {
        $deputyUid = '12121212';
        $caseNumber = '12345678';
        $orderUid = '66667777';

        // add staging deputyship
        $deputyship = new StagingDeputyship();
        $deputyship->orderUid = $orderUid;
        $deputyship->deputyUid = $deputyUid;
        $deputyship->deputyType = $deputyType;
        $deputyship->orderType = $orderType;
        $deputyship->isHybrid = $isHybrid;
        $deputyship->caseNumber = $caseNumber;

        $this->em->persist($deputyship);
        $this->em->flush();

        // add client
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->em->persist($client);
        $this->em->flush();

        // add compatible report
        $report1 = new Report(
            client: $client,
            type: $reportType,
            startDate: new \DateTime(),
            endDate: new \DateTime(),
            dateChecks: false
        );

        $this->em->persist($report1);
        $this->em->flush();

        // add an incompatible report (just to make sure we don't pick it up as compatible)
        $incompatibleReportType = '104';
        if ('hw' == $orderType) {
            $incompatibleReportType = '102';
        }
        $report2 = new Report(
            client: $client,
            type: $incompatibleReportType,
            startDate: new \DateTime(),
            endDate: new \DateTime(),
            dateChecks: false
        );

        $this->em->persist($report2);
        $this->em->flush();

        // find compatible reports
        $candidates = $this->sut->createCompatibleReportCandidates();

        // assertions
        self::assertCount(1, $candidates);
        self::assertEquals(StagingSelectedCandidate::INSERT_ORDER_REPORT, $candidates[0]->action);
        self::assertEquals($orderUid, $candidates[0]->orderUid);
        self::assertEquals($report1->getId(), $candidates[0]->reportId);
    }
}
