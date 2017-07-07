<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Casrec as CasRecEntity;
use AppBundle\Entity\Report\Asset as AssetEntity;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Report\Report as ReportEntity;

use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Client;

class ReportServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var Client
     */
    protected $frameworkBundleClient;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Fixtures
     */
    protected $fixtures;

    /**
     * @var ReportService
     */
    protected $sut;

    private $repos;

    public function setUp()
    {
        $this->repos[ReportEntity::class] = m::mock(EntityDir\Repository\ReportRepository::class);
        $this->repos[CasRecEntity::class] = m::mock(EntityDir\Repository\CasRecRepository::class);
        $this->repos[AssetEntity::class] = m::mock();
        $this->repos[BankAccountEntity::class] = m::mock();

        $this->em = m::mock(EntityManager::class);
        $this->em->shouldReceive('getRepository')
            ->with(ReportEntity::class)
            ->andReturn($this->repos[ReportEntity::class]);
        $this->em->shouldReceive('getRepository')
            ->with(CasRecEntity::class)->andReturn($this->repos[CasRecEntity::class]);
        $this->em->shouldReceive('getRepository')
            ->with(AssetEntity::class)->andReturn($this->repos[AssetEntity::class]);
        $this->em->shouldReceive('getRepository')
            ->with(BankAccountEntity::class)
            ->andReturn($this->repos[BankAccountEntity::class]);

        $this->sut = new ReportService($this->repos[ReportEntity::class], $this->repos[CasRecEntity::class], $this->em);
    }

    /**
     * this test could be refactor moking reportService.setReportTypeBasedOnCasrec
     *
     * @test
     * @dataProvider createNextYearReportChangesTypeProvider
     */
    public function testCreateNextYearReportChangesType($initialType, $corref, $casRecTypeOfReport, $newReportType)
    {
        if ($initialType === ReportEntity::TYPE_103 && !ReportEntity::ENABLE_103) {
            $this->markTestSkipped('enable when 103 is enabled');
        }

        if ($initialType === ReportEntity::TYPE_104 && !ReportEntity::ENABLE_104) {
            $this->markTestSkipped('enable when 104 is enabled');
        }

        $casRecEntity = m::mock(CasRecEntity::class)->makePartial();
        $casRecEntity->shouldReceive('getCorref')->andReturn($corref);
        $casRecEntity->shouldReceive('getTypeOfReport')->andReturn($casRecTypeOfReport);

        $mockBankAccount = m::mock(BankAccountEntity::class);
        $mockBankAccount->shouldReceive('getBank')->once()->andReturn('bank');
        $mockBankAccount->shouldReceive('getAccountType')->once()->andReturn('account_type');
        $mockBankAccount->shouldReceive('getSortCode')->once()->andReturn('010101');
        $mockBankAccount->shouldReceive('getAccountNumber')->once()->andReturn('90909090');
        $mockBankAccount->shouldReceive('getOpeningBalance')->andReturn(999);
        $mockBankAccount->shouldReceive('getClosingBalance')->once()->andReturn(999);

        $mockAsset = m::mock(AssetEntity::class)->makePartial();
        $mockAsset->shouldReceive('setReport')->once()->with(m::type(ReportEntity::class));

        $report = m::mock(ReportEntity::class, [
            'getType' => $initialType,
            'getEndDate' => new \DateTime(),
            'getStartDate' => new \DateTime(),
            'getAssets' => [$mockAsset],
            'getBankAccounts' => [$mockBankAccount],
            'getAssetsTotalValue' => 999,
            'getCaseNumber' => 1111,
            'getSubmitted' => true
        ])->makePartial();

        $client = new EntityDir\Client();
        $report->setClient($client);

        $this->repos[CasRecEntity::class]->shouldReceive('findOneBy')
            ->with(['caseNumber' => $client->getCaseNumber()])
            ->andReturn($casRecEntity);

        // assert Asset saved
        $this->em->shouldReceive('detach')->once()->with(m::type(AssetEntity::class));
        $this->em->shouldReceive('persist')->once()->with(m::type(AssetEntity::class));

        // assert BankAccount saved
        $this->em->shouldReceive('persist')->once()->with(m::type(BankAccountEntity::class));

        // Assert newReport saved
        $this->em->shouldReceive('persist')->once()->with(m::type(ReportEntity::class));
        $this->em->shouldReceive('flush')->once();

        $newReport = $this->sut->createNextYearReport($report);
        $this->assertEquals($newReportType, $newReport->getType());
        $this->assertContainsOnlyInstancesOf(BankAccountEntity::class, $newReport->getBankAccounts());
        $this->assertContainsOnlyInstancesOf(AssetEntity::class, $newReport->getAssets());
    }

    public static function createNextYearReportChangesTypeProvider()
    {
        return [
            // more cases on CasrecTest::getTypeBasedOnTypeofRepAndCorrefProvider
            [ReportEntity::TYPE_102, 'l3', 'opg103', ReportEntity::TYPE_103],
            [ReportEntity::TYPE_103, 'l2', 'opg102', ReportEntity::TYPE_102],
        ];
    }
}
