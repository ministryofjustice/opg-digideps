<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Casrec as CasRecEntity;
use AppBundle\Entity\Report\Asset as AssetEntity;
use AppBundle\Entity\Report\Asset;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report as ReportEntity;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use MockeryStub as m;
use Symfony\Bundle\FrameworkBundle\Client;

class ReportServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $user;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var Fixtures
     */
//    protected $fixtures;

    /**
     * @var ReportService
     */
    protected $sut;

    private $repos;

    public function setUp()
    {
        $this->user = new EntityDir\User();
        $client = new EntityDir\Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $this->report = new Report($client, ReportEntity::TYPE_102, new \DateTime('2015-01-31'), new \DateTime('2015-12-31'));
        $this->asset1 = (new EntityDir\Report\AssetProperty())->setAddress('SW1');
        $this->report->setNoAssetToAdd(false)->addAsset($this->asset1);
        $this->bank1 = (new BankAccount())->setAccountNumber('1234');
        $this->report->addAccount($this->bank1);

        // mock em
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

        $this->repos[CasRecEntity::class]->shouldReceive('findOneBy')
            ->with(['caseNumber' => $client->getCaseNumber()])
            ->andReturn(null); // can be tested separately, currently partially covered by CASREC class

        $this->sut = new ReportService($this->repos[ReportEntity::class], $this->repos[CasRecEntity::class], $this->em);
    }


    public function testSubmitInvalid()
    {
        $this->report->setAgreedBehalfDeputy(false);
        $this->setExpectedException(\RuntimeException::class, 'agreed');
        $this->sut->submit($this->report, $this->user, new \DateTime('2016-01-15'));
    }

    public function testSubmitValid()
    {
        // mocks
        $this->em->shouldReceive('flush')->with($this->report)->once();
        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('flush');
        $this->em->shouldReceive('persist')->with(\Mockery::on(function($report) {
            return $report instanceof Report;
        }));
        // assert asset and bank accounts are copied. can't get from the returned report as they are added form the "Many" side
        $this->em->shouldReceive('persist')->with(\Mockery::on(function($asset) {
            return $asset instanceof EntityDir\Report\AssetProperty && $asset->getAddress() === 'SW1';
        }))->once();
        $this->em->shouldReceive('persist')->with(\Mockery::on(function($bankAccount) {
            return $bankAccount instanceof EntityDir\Report\BankAccount && $bankAccount->getAccountNumber() === '1234';
        }))->once();

        $this->report->setAgreedBehalfDeputy(true);
        $newYearReport = $this->sut->submit($this->report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($this->report->getSubmitted());

        //assert new year report
        $this->assertEquals($this->report->getType(), $newYearReport->getType());
        $this->assertEquals('2016-01-01', $newYearReport->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2016-12-31', $newYearReport->getEndDate()->format('Y-m-d'));
    }

    public function tearDown()
    {
        m::close();
    }

}
