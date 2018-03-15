<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Casrec as CasRecEntity;
use AppBundle\Entity\Report\Asset;
use AppBundle\Entity\Report\Asset as AssetEntity;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Report\Report;

use AppBundle\Entity\Report\Report as ReportEntity;
use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use MockeryStub as m;

class ReportServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityDir\User
     */
    private $user;

    /**
     * @var Report
     */
    private $report;

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
        $this->bank1 = (new BankAccount())->setAccountNumber('1234');
        $this->asset1 = (new EntityDir\Report\AssetProperty())->setAddress('SW1');
        $this->report = new Report($client, ReportEntity::TYPE_102, new \DateTime('2015-01-01'), new \DateTime('2015-12-31'));
        $this->report
            ->setNoAssetToAdd(false)
            ->addAsset($this->asset1)
            ->addAccount($this->bank1)
            ->setSubmittedBy($this->user);

        $this->document1 = (new EntityDir\Report\Document($this->report))->setFileName('file1.pdf');
        $this->report->addDocument($this->document1);

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
        $report = $this->report;

        // mocks
        $this->em->shouldReceive('detach');
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof Report;
        }));
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));
        // assert asset and bank accounts are copied. can't get from the returned report as they are added form the "Many" side
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($asset) {
            return $asset instanceof EntityDir\Report\AssetProperty && $asset->getAddress() === 'SW1';
        }))->once();
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($bankAccount) {
            return $bankAccount instanceof EntityDir\Report\BankAccount && $bankAccount->getAccountNumber() === '1234';
        }))->once();
        $this->em->shouldReceive('flush')->with()->once(); //last in createNextYearReport

        $report->setAgreedBehalfDeputy(true);
        $newYearReport = $this->sut->submit($report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($report->getSubmitted());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first();
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        //assert new year report
        $this->assertEquals($report->getType(), $newYearReport->getType());
        $this->assertEquals('2016-01-01', $newYearReport->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2016-12-31', $newYearReport->getEndDate()->format('Y-m-d'));
    }

    public function testResubmit()
    {
        $report = $this->report;
        $report->setUnSubmitDate(new \DateTime('2018-02-14'));

        // mocks
        $this->em->shouldReceive('detach');
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('flush')->with()->once(); //last in createNextYearReport

        $report->setAgreedBehalfDeputy(true);
        $newYearReport = $this->sut->submit($report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($report->getSubmitted());
        $this->assertNull($report->getUnSubmitDate());
        $this->assertNull($report->getUnsubmittedSectionsList());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first();
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        //assert new year report
        $this->assertNull($newYearReport);
    }

    public function testSubmitAdditionalDocuments()
    {
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('flush')->with()->once();

        $this->assertEmpty($this->report->getReportSubmissions());
        $currentReport = $this->sut->submitAdditionalDocuments($this->report, $this->user, new \DateTime('2016-01-15'));
        $submission = $currentReport->getReportSubmissions()->first();

        $this->assertContains($submission, $this->report->getReportSubmissions());
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($this->report->getSubmittedBy(), $submission->getCreatedBy());
    }

    public function tearDown()
    {
        m::close();
    }
}
