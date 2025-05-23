<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\StagingDeputyship;
use App\v2\Registration\DeputyshipProcessing\CourtOrderReportCandidatesFactory;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
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

    // create a report which is not compatible with a deputyship (CSV row)
    private function createIncompatibleReport(Client $client, string $orderType, string $deputyType): Report
    {
        // a 104 is not compatible with a hybrid or pfa deputyship
        $incompatibleReportType = '104';
        if ('hw' === $orderType) {
            // a 102 is not compatible with a hybrid or hw deputyship
            $incompatibleReportType = '102';
        }

        if ('PA' === $deputyType) {
            $incompatibleReportType .= '-6';
        } elseif ('PRO' === $deputyType) {
            $incompatibleReportType .= '-5';
        }

        return new Report(
            client: $client,
            type: $incompatibleReportType,
            startDate: new \DateTime(),
            endDate: new \DateTime(),
            dateChecks: false
        );
    }

    private function compatibleReportDataProvider(): array
    {
        return [
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '0', 'compatibleReportType' => '102'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '0', 'compatibleReportType' => '103'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '0', 'compatibleReportType' => '104'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '102-4'],
            ['deputyType' => 'LAY', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '103-4'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '102-4'],
            ['deputyType' => 'LAY', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '103-4'],

            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '0', 'compatibleReportType' => '102-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '0', 'compatibleReportType' => '103-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '0', 'compatibleReportType' => '104-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '102-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '103-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '102-4-6'],
            ['deputyType' => 'PA', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '103-4-6'],

            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '0', 'compatibleReportType' => '102-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '0', 'compatibleReportType' => '103-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '0', 'compatibleReportType' => '104-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '102-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'pfa', 'isHybrid' => '1', 'compatibleReportType' => '103-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '102-4-5'],
            ['deputyType' => 'PRO', 'orderType' => 'hw', 'isHybrid' => '1', 'compatibleReportType' => '103-4-5'],
        ];
    }

    /**
     * @dataProvider compatibleReportDataProvider
     */
    public function testCreateCompatibleReportCandidates(
        string $deputyType,
        string $orderType,
        string $isHybrid,
        string $compatibleReportType,
    ): void {
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
            type: $compatibleReportType,
            startDate: new \DateTime(),
            endDate: new \DateTime(),
            dateChecks: false
        );

        $this->em->persist($report1);
        $this->em->flush();

        // add an incompatible report (just to make sure we don't pick it up as compatible)
        $report2 = $this->createIncompatibleReport($client, $orderType, $deputyType);

        $this->em->persist($report2);
        $this->em->flush();

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

        // add pfa/LAY staging deputyship
        $deputyship = new StagingDeputyship();
        $deputyship->deputyUid = '11112233';
        $deputyship->orderUid = $orderUid;
        $deputyship->deputyType = 'LAY';
        $deputyship->orderType = 'pfa';
        $deputyship->caseNumber = $caseNumber;

        $this->em->persist($deputyship);
        $this->em->flush();

        // add client
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->em->persist($client);
        $this->em->flush();

        // add NDR to client
        $ndr = new Ndr($client);

        $this->em->persist($ndr);
        $this->em->flush();

        // create NDR candidates
        $candidates = iterator_to_array($this->sut->createCompatibleNdrCandidates());

        // assertions
        self::assertCount(1, $candidates);
        self::assertEquals(DeputyshipCandidateAction::InsertOrderNdr, $candidates[0]->action);
        self::assertEquals($orderUid, $candidates[0]->orderUid);
        self::assertEquals($ndr->getId(), $candidates[0]->ndrId);
    }
}
