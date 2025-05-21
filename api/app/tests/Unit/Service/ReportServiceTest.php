<?php

namespace App\Tests\Unit\Service;

use App\Entity as EntityDir;
use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Asset;
use App\Entity\Report\AssetProperty;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\AssetRepository;
use App\Repository\BankAccountRepository;
use App\Repository\DocumentRepository;
use App\Repository\PreRegistrationRepository;
use App\Repository\ReportRepository;
use App\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery\MockInterface;
use MockeryStub as m;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ReportServiceTest extends TestCase
{
    use ProphecyTrait;

    private User $user;
    private BankAccount $bank1;
    private AssetProperty $asset1;
    private Report $report;
    private Document $document1;
    private EntityDir\Ndr\Ndr $ndr;
    private EntityRepository|MockInterface $casrecRepo;
    private MockInterface $assetRepo;
    private MockInterface $bankAccount;
    private MockInterface|EntityManager $em;
    private Document $mockNdrDocument;
    private LoggerInterface&MockObject $mockLogger;
    private ReportService $sut;

    public function setUp(): void
    {
        $this->user = new User();
        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new \DateTime('2014-06-06'));

        $this->bank1 = (new BankAccount())->setAccountNumber('1234');
        $this->asset1 = (new AssetProperty())
            ->setAddress('SW1')
            ->setOwned(AssetProperty::OWNED_FULLY);
        $this->report = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2015-01-01'), new \DateTime('2015-12-31'));
        $this->report
            ->setNoAssetToAdd(false)
            ->addAsset($this->asset1)
            ->addAccount($this->bank1)
            ->setSubmittedBy($this->user);

        $this->document1 = (new Document($this->report))->setFileName('file1.pdf');
        $this->report->addDocument($this->document1);
        $this->ndr = new EntityDir\Ndr\Ndr($client);

        // mock em
        $this->casrecRepo = m::mock(EntityRepository::class);
        $this->assetRepo = m::mock();
        $this->bankAccount = m::mock();

        $this->em = m::mock(EntityManager::class);
        $this->mockNdrDocument = (new Document($this->ndr))->setFileName('NdrRep-file2.pdf')->setId(999);

        $this->em->shouldReceive('getRepository')->andReturnUsing(function ($arg) use ($client) {
            switch ($arg) {
                case PreRegistration::class:
                    return m::mock(EntityRepository::class)->shouldReceive('findOneBy')
                        ->with(['caseNumber' => $client->getCaseNumber()])
                        ->andReturn(null)
                        ->getMock();
                case Report::class:
                    return m::mock(ReportRepository::class);
                case Asset::class:
                    return m::mock(EntityRepository::class);
                case BankAccount::class:
                    return m::mock(BankAccount::class);
                case Document::class:
                    return m::mock(DocumentRepository::class)
                        ->shouldReceive('find')
                        ->zeroOrMoreTimes()
                        ->with(999)
                        ->andReturn($this->mockNdrDocument)
                        ->getMock();
            }
        });

        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->sut = new ReportService($this->em, $this->mockLogger);
    }

    public function testSubmitInvalid()
    {
        $this->report->setAgreedBehalfDeputy(false);
        $this->expectException(\RuntimeException::class);
        $this->sut->submit($this->report, $this->user, new \DateTime('2016-01-15'));
    }

    public function testSubmitValid()
    {
        $report = $this->report;

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em, $this->mockLogger])->makePartial();

        // mocks
        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('flush');
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof Report;
        }));
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));

        // clonePersistentResources should be called
        $reportService->shouldReceive('clonePersistentResources')->with(\Mockery::type(Report::class), $report);

        $report->setAgreedBehalfDeputy(true);
        $newYearReport = $reportService->submit($report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($report->getSubmitted());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first();
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        // assert new year report
        $this->assertEquals($report->getType(), $newYearReport->getType());
        $this->assertEquals('2016-01-01', $newYearReport->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2016-12-31', $newYearReport->getEndDate()->format('Y-m-d'));
    }

    public function testResubmit()
    {
        $report = $this->report;
        $report->setUnSubmitDate(new \DateTime('2018-02-14'));

        // A report for the next report period should already exist
        $client = $this->report->getClient();
        $nextReport = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));
        $client->addReport($nextReport);

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em, $this->mockLogger])->makePartial();

        // mocks
        $this->em->shouldReceive('detach');
        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('flush')->with()->once(); // last in createNextYearReport

        // clonePersistentResources should be called
        $reportService->shouldReceive('clonePersistentResources')->with($nextReport, $report);

        $report->setAgreedBehalfDeputy(true);
        $newYearReport = $reportService->submit($report, $this->user, new \DateTime());

        // assert current report
        $this->assertTrue($report->getSubmitted());
        $this->assertNull($report->getUnSubmitDate());
        $this->assertNull($report->getUnsubmittedSectionsList());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first();
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        // assert new year report
        $this->assertEquals($newYearReport, $nextReport);
    }

    public function testSubmitNotAgreedNdrThrowsException()
    {
        $this->expectException(\RuntimeException::class);

        $this->ndr->setAgreedBehalfDeputy(null);
        $submitDate = new \DateTime('2018-04-05');

        $ndrDoccumentId = 999;

        $this->sut->submit($this->ndr, $this->user, $submitDate, $ndrDoccumentId);
    }

    public function testSubmitValidNdr()
    {
        $ndr = $this->getFilledInNdr();

        $submitDate = new \DateTime('2018-04-05');

        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($ndr) {
            return $ndr instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof Report;
        }));
        $this->em->shouldReceive('flush')->with()->once(); // last in createNextYearReport

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em, $this->mockLogger])->makePartial();
        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');

        /** @var Report $newYearReport */
        $newYearReport = $reportService->submit($ndr, $this->user, $submitDate, 999);

        // assert current report
        $this->assertTrue($ndr->getSubmitted());

        // assert new year report
        $this->assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $newYearReport->getType());
        $this->assertEquals('06-06', $newYearReport->getStartDate()->format('m-d'));
        $this->assertEquals('06-05', $newYearReport->getEndDate()->format('m-d'));

        // assert assets/accounts added
        $newAsset = $newYearReport->getAssets()->first();
        $newAccount = $newYearReport->getBankAccounts()->first();

        $this->assertInstanceOf(Asset::class, $newAsset);
        $this->assertInstanceOf(BankAccount::class, $newAccount);
        $this->assertEquals('SW1', $newAsset->getAddress());
        $this->assertEquals('4321', $newAccount->getAccountNumber());
    }

    private function getFilledInNdr()
    {
        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new \DateTime('2014-06-06'));

        $ndr = new EntityDir\Ndr\Ndr($client);

        $ndrBank = new EntityDir\Ndr\BankAccount();
        $ndrBank->setAccountNumber('4321')
            ->setNdr($ndr);

        $ndrAsset = new EntityDir\Ndr\AssetProperty();
        $ndrAsset->setAddress('SW1')
            ->setOwned(AssetProperty::OWNED_FULLY)
            ->setNdr($ndr);

        $ndr->setNoAssetToAdd(false);
        $ndr->addAsset($ndrAsset);
        $ndr->setBankAccounts([$ndrBank]);
        $ndr->setAgreedBehalfDeputy(true);
        $ndr->setClient($client);

        return $ndr;
    }

    public function testSubmitNdrWithExistingReport()
    {
        $ndr = $this->getFilledInNdr();

        $report = new Report($ndr->getClient(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2018-06-06'), new \DateTime('2019-06-05'));
        $ndr->getClient()->addReport($report);

        $submitDate = new \DateTime('2018-04-05');

        // assert persists on report and submission record
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($ndr) {
            return $ndr instanceof EntityDir\Report\ReportSubmission;
        }));
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($report) {
            return $report instanceof Report;
        }));
        $this->em->shouldReceive('flush')->with()->once(); // last in createNextYearReport

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em, $this->mockLogger])->makePartial();
        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');

        /** @var Report $newYearReport */
        $newYearReport = $reportService->submit($ndr, $this->user, $submitDate, 999);

        // assert existing report carries over
        $this->assertEquals($report->getId(), $newYearReport->getId());
        $this->assertCount(1, $ndr->getClient()->getReports());

        // assert assets/accounts added
        $newAsset = $report->getAssets()->first();
        $newAccount = $report->getBankAccounts()->first();

        $this->assertInstanceOf(Asset::class, $newAsset);
        $this->assertInstanceOf(BankAccount::class, $newAccount);
        $this->assertEquals('SW1', $newAsset->getAddress());
        $this->assertEquals('4321', $newAccount->getAccountNumber());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testResubmitPersistenceRequiresReport()
    {
        $report = $this->report;
        $report->setUnSubmitDate(new \DateTime('2018-02-14'));
        $report->setAgreedBehalfDeputy(true);

        // Create partial mock of ReportService
        $reportService = \Mockery::mock(ReportService::class, [$this->em, $this->mockLogger])->makePartial();
        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');

        // Assert that clonePersistentResources should not be called
        $reportService->shouldNotReceive('clonePersistentResources');

        // Submit a report without one set up for next year
        $reportService->submit($report, $this->user, new \DateTime());

        // Submit a report where next year's dates don't match
        $client = $this->report->getClient();
        $nextReport = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-17'), new \DateTime('2017-01-16'));
        $client->addReport($nextReport);

        $report->setUnSubmitDate(new \DateTime('2018-02-14'));
        $report->setAgreedBehalfDeputy(true);

        $reportService->submit($report, $this->user, new \DateTime());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testPersistentResourcesCloned()
    {
        $client = $this->report->getClient();
        $newReport = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));

        // Assert asset is cloned
        $this->em->shouldReceive('detach')->once();
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($asset) {
            return $asset instanceof AssetProperty
                && 'SW1' === $asset->getAddress();
        }))->once();

        // Assert bank account is cloned, with opening/closing balance modified
        $this->em->shouldReceive('persist')->with(\Mockery::on(function ($bankAccount) {
            return $bankAccount instanceof BankAccount
                && '1234' === $bankAccount->getAccountNumber()
                && $bankAccount->getOpeningBalance() === $this->report->getBankAccounts()[0]->getClosingBalance()
                && is_null($bankAccount->getClosingBalance());
        }))->once();

        $this->em->shouldReceive('flush');

        $this->sut->clonePersistentResources($newReport, $this->report);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDuplicateResourcesNotPersisted()
    {
        $client = $this->report->getClient();
        $newReport = new Report($client, Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));

        $newAsset = clone $this->report->getAssets()[0];
        $newReport->addAsset($newAsset);

        $newAccount = clone $this->report->getBankAccounts()[0];
        $newReport->addAccount($newAccount);

        // Since assets and accounts already exist, no DB functions should be called
        $this->em->shouldNotReceive('detach');
        $this->em->shouldNotReceive('persist');
        $this->em->shouldNotReceive('flush');

        $this->sut->clonePersistentResources($newReport, $this->report);
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

    public function testIsDue()
    {
        $this->assertEquals(false, ReportService::isDue(null));

        $todayMidnight = new \DateTime('today midnight');

        $oneMinuteBeforeLastMidnight = clone $todayMidnight;
        $oneMinuteBeforeLastMidnight->modify('-1 minute');

        $oneMinuteAfterLastMidnight = clone $todayMidnight;
        $oneMinuteAfterLastMidnight->modify('+1 minute');

        // end date is past (before midnight) => due
        $this->assertEquals(true, ReportService::isDue(new \DateTime('last week')));
        $this->assertEquals(true, ReportService::isDue($oneMinuteBeforeLastMidnight));

        // otherwise not due
        $this->assertEquals(false, ReportService::isDue($oneMinuteAfterLastMidnight));
        $this->assertEquals(false, ReportService::isDue(new \DateTime('next week')));
    }

    /**
     * @dataProvider getReportTypeBasedOnSiriusProvider
     */
    public function testGetReportTypeBasedOnSirius(Client $client, bool $isAString)
    {
        $preRegistration = new PreRegistration(['ReportType' => 'OPG103', 'OrderType' => 'pfa']);

        /** @var ObjectProphecy|PreRegistrationRepository $preRegistrationRepo */
        $preRegistrationRepo = self::prophesize(PreRegistrationRepository::class);
        $preRegistrationRepo->findOneBy(['caseNumber' => '12345678'])->willReturn($preRegistration);

        $reportRepository = self::prophesize(ReportRepository::class);
        $assetRepository = self::prophesize(AssetRepository::class);
        $bankAccountRepository = self::prophesize(BankAccountRepository::class);

        $em = self::prophesize(EntityManagerInterface::class);
        $em->getRepository(PreRegistration::class)->willReturn($preRegistrationRepo->reveal());
        $em->getRepository(Asset::class)->willReturn($assetRepository->reveal());
        $em->getRepository(BankAccount::class)->willReturn($bankAccountRepository->reveal());

        $sut = new ReportService($em->reveal(), $this->mockLogger);

        self::assertEquals($isAString, is_string($sut->getReportTypeBasedOnSirius($client)));
    }

    public function getReportTypeBasedOnSiriusProvider()
    {
        $lay = (new User())->setRoleName(User::ROLE_LAY_DEPUTY);
        $prof = (new User())->setRoleName(User::ROLE_PROF_ADMIN);
        $pa = (new User())->setRoleName(User::ROLE_PA_ADMIN);

        $layClient = (new Client())
            ->addUser($lay)
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        $profClient = (new Client())
            ->addUser($prof)
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        $paClient = (new Client())
            ->addUser($pa)
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        $noUserClient = (new Client())
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        return [
            'layUserAttachedToClient' => [$layClient, true],
            'profUserAttachedToClient' => [$profClient, false],
            'paUserAttachedToClient' => [$paClient, false],
            'noUsersAttached' => [$noUserClient, false],
        ];
    }

    public function testUserStatusIsSetToActiveOnceReportIsSubmitted()
    {
        $user = $this->user->setActive(null);

        $reportService = \Mockery::mock(ReportService::class, [$this->em, $this->mockLogger])->makePartial();

        $this->em->shouldReceive('detach');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');

        $this->report->setAgreedBehalfDeputy(true);

        $reportService->submit($this->report, $user, new \DateTime());

        $this->assertTrue($user->getActive());
    }

    public function tearDown(): void
    {
        m::close();
    }
}
