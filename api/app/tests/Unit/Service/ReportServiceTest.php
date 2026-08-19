<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\AssetProperty;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\PreRegistrationRepository;
use OPG\Digideps\Backend\Service\ReportService;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ReportServiceTest extends TestCase
{
    private User $user;
    private Report $report;
    private Document $document1;
    private EntityManager&MockObject $em;
    private PreRegistrationRepository&MockObject $mockPreRegistrationRepository;
    private ReportService $sut;

    public function setUp(): void
    {
        $this->user = new User('', '', '');
        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new \DateTime('2014-06-06'));

        $this->report = new Report($this->makeCourtOrder($client), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2015-01-01'), new \DateTime('2015-12-31'));
        $this->report->setNoAssetToAdd(false)
            ->addAsset(
                new AssetProperty($this->report)
                    ->setAddress('SW1')
                    ->setOwned(AssetProperty::OWNED_FULLY)
            )
            ->addAccount(new BankAccount($this->report)->setAccountNumber('1234'))
            ->setSubmittedBy($this->user);

        $this->document1 = new Document($this->report, 'file1.pdf');
        $this->report->addDocument($this->document1);

        $this->em = self::createMock(EntityManager::class);

        $this->em->method('getRepository')->willReturnCallback(function ($arg) {
            if ($arg !== PreRegistration::class) {
                return null;
            }

            $this->mockPreRegistrationRepository = self::createMock(PreRegistrationRepository::class);

            return $this->mockPreRegistrationRepository;
        });

        $this->sut = new ReportService($this->em, new NullLogger());
    }

    public function testSubmitInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->report->setAgreedBehalfDeputy('foo');

        $this->sut->submit($this->report, $this->user, new \DateTime('2016-01-15'));
    }

    public function testSubmitValid(): void
    {
        $report = $this->report;
        $report->setAgreedBehalfDeputy('only_deputy');

        $reportService = new ReportService($this->em, new NullLogger());
        $newYearReport = $reportService->submit($report, $this->user, new \DateTime('2016-01-15'));

        // assert current report
        $this->assertTrue($report->getSubmitted());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first() ?: null;
        $this->assertNotNull($submission);
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        // assert new year report
        $this->assertEquals($report->getType(), $newYearReport->getType());
        $this->assertEquals('2016-01-01', $newYearReport->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2016-12-31', $newYearReport->getEndDate()->format('Y-m-d'));
    }

    public function testResubmit(): void
    {
        $report = $this->report;
        $report->setUnSubmitDate(new \DateTime('2018-02-14'));

        // A report for the next report period should already exist
        $client = $this->report->getClient();
        $nextReport = new Report($this->makeCourtOrder($client), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));
        $client->addReport($nextReport);

        $reportService = new ReportService($this->em, new NullLogger());

        $report->setAgreedBehalfDeputy('only_deputy');
        $newYearReport = $reportService->submit($report, $this->user, new \DateTime());

        // assert current report
        $this->assertTrue($report->getSubmitted());
        $this->assertNull($report->getUnSubmitDate());
        $this->assertNull($report->getUnsubmittedSectionsList());

        // assert reportsubmissions
        $submission = $report->getReportSubmissions()->first() ?: null;
        $this->assertNotNull($submission);
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($report->getSubmittedBy(), $submission->getCreatedBy());

        // assert new year report
        $this->assertEquals($newYearReport, $nextReport);
    }

    #[DoesNotPerformAssertions]
    public function testResubmitPersistenceRequiresReport(): void
    {
        $report = $this->report;
        $report->setUnSubmitDate(new \DateTime('2018-02-14'));
        $report->setAgreedBehalfDeputy('only_deputy');

        $reportService = new ReportService($this->em, new NullLogger());

        // Submit a report without one set up for next year
        $reportService->submit($report, $this->user, new \DateTime());

        // Submit a report where next year's dates don't match
        $client = $this->report->getClient();
        $nextReport = new Report($this->makeCourtOrder($client), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-17'), new \DateTime('2017-01-16'));
        $client->addReport($nextReport);

        $report->setUnSubmitDate(new \DateTime('2018-02-14'));
        $report->setAgreedBehalfDeputy('only_deputy');

        $reportService->submit($report, $this->user, new \DateTime());
    }

    public function testDuplicateResourcesNotPersisted(): void
    {
        $client = $this->report->getClient();
        $newReport = new Report($this->makeCourtOrder($client), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime('2016-01-01'), new \DateTime('2016-12-31'));

        $oldAsset = $this->report->getAssets()[0];
        $this->assertNotNull($oldAsset);
        $newAsset = clone $oldAsset;
        $newReport->addAsset($newAsset);

        $oldAccount = $this->report->getBankAccounts()[0];
        $this->assertNotNull($oldAccount);
        $newAccount = clone $oldAccount;
        $newReport->addAccount($newAccount);

        // Since assets and accounts already exist, no DB functions should be called
        $this->em->expects(self::never())->method('detach');
        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::never())->method('flush');

        $this->sut->clonePersistentResources($newReport, $this->report);
    }

    public function testSubmitAdditionalDocuments(): void
    {
        $this->assertEmpty($this->report->getReportSubmissions());
        $currentReport = $this->sut->submitAdditionalDocuments($this->report, $this->user, new \DateTime('2016-01-15'));
        $submission = $currentReport->getReportSubmissions()->first() ?: null;
        $this->assertNotNull($submission);
        $this->assertContains($submission, $this->report->getReportSubmissions());
        $this->assertEquals($this->document1, $submission->getDocuments()->first());
        $this->assertEquals($this->report->getSubmittedBy(), $submission->getCreatedBy());
    }

    public function testIsDue(): void
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
        $this->assertEquals(false, ReportService::isDue($todayMidnight));
    }

    #[DataProvider('getReportTypeBasedOnSiriusProvider')]
    public function testGetReportTypeBasedOnSirius(Client $client, bool $isAString): void
    {
        $preRegistration = new PreRegistration(['ReportType' => 'OPG103', 'OrderType' => 'pfa']);

        $preRegistrationRepo = self::createMock(PreRegistrationRepository::class);
        $preRegistrationRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['caseNumber' => '12345678'])
            ->willReturn($preRegistration);

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('getRepository')
            ->with(PreRegistration::class)
            ->willReturn($preRegistrationRepo);

        $sut = new ReportService($em, new NullLogger());

        self::assertEquals($isAString, is_string($sut->getReportTypeBasedOnSirius($client)));
    }

    public static function getReportTypeBasedOnSiriusProvider(): array
    {
        $lay = new User('', '', '')->setRoleName(User::ROLE_LAY_DEPUTY);
        $prof = new User('', '', '')->setRoleName(User::ROLE_PROF_ADMIN);
        $pa = new User('', '', '')->setRoleName(User::ROLE_PA_ADMIN);

        $layClient = new Client()
            ->addUser($lay)
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        $profClient = new Client()
            ->addUser($prof)
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        $paClient = new Client()
            ->addUser($pa)
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        $noUserClient = new Client()
            ->setCaseNumber('12345678')
            ->setCourtDate(new \DateTime('2014-06-06'));

        return [
            'layUserAttachedToClient' => [$layClient, true],
            'profUserAttachedToClient' => [$profClient, false],
            'paUserAttachedToClient' => [$paClient, false],
            'noUsersAttached' => [$noUserClient, false],
        ];
    }

    public function testUserStatusIsSetToActiveOnceReportIsSubmitted(): void
    {
        $user = $this->user->setActive(false);

        $reportService = new ReportService($this->em, new NullLogger());

        $this->report->setAgreedBehalfDeputy('only_deputy');

        $reportService->submit($this->report, $user, new \DateTime());

        $this->assertTrue($user->getActive());
    }

    #[DataProvider('provideForDetermineStartDateOfFirstReport')]
    public function testDetermineStartDateOfFirstReport(\DateTimeImmutable $now, \DateTimeImmutable $madeDate, ?\DateTimeImmutable $expectedStartDate): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($this->createStub(PreRegistrationRepository::class));

        $reportService = new ReportService($em, new NullLogger(), $now);
        $courtOrder = $this->createStub(CourtOrder::class);
        $courtOrder->method('getOrderMadeDate')->willReturn(\DateTime::createFromImmutable($madeDate));

        if ($expectedStartDate === null) {
            $this->expectException(\DomainException::class);
        }
        $startDate = $reportService->determineStartDateOfFirstReport($courtOrder);
        if ($expectedStartDate !== null) {
            $this->assertSame($expectedStartDate->format('Y-m-d H:i:s'), $startDate->format('Y-m-d H:i:s'));
        }
    }

    public static function provideForDetermineStartDateOfFirstReport(): array
    {
        return array_map(
            fn (array $scenario) => array_map(fn (?string $date): ?\DateTimeImmutable => $date ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date) ?: null: null, $scenario),
            [
                ['2023-03-15 00:00:00', '2023-06-15 00:00:00', null],
                ['2023-06-14 23:59:59', '2023-06-15 00:00:00', null],
                ['2023-06-15 00:00:00', '2023-06-15 00:00:00', '2023-06-15 00:00:00'],
                ['2023-09-15 00:00:00', '2023-06-15 00:00:00', '2023-06-15 00:00:00'],

                ['2024-03-15 00:00:00', '2023-06-15 00:00:00', '2023-06-15 00:00:00'],
                ['2024-06-14 23:59:59', '2023-06-15 00:00:00', '2023-06-15 00:00:00'],
                ['2024-06-15 00:00:00', '2023-06-15 00:00:00', '2024-06-15 00:00:00'],
                ['2024-09-15 00:00:00', '2023-06-15 00:00:00', '2024-06-15 00:00:00'],

                ['2025-03-15 00:00:00', '2023-06-15 00:00:00', '2024-06-15 00:00:00'],
                ['2025-06-14 23:59:59', '2023-06-15 00:00:00', '2024-06-15 00:00:00'],
                ['2025-06-15 00:00:00', '2023-06-15 00:00:00', '2025-06-15 00:00:00'],
                ['2025-09-15 00:00:00', '2023-06-15 00:00:00', '2025-06-15 00:00:00'],

                ['2026-03-15 00:00:00', '2023-06-15 00:00:00', '2025-06-15 00:00:00'],
                ['2026-06-14 23:59:59', '2023-06-15 00:00:00', '2025-06-15 00:00:00'],
                ['2026-06-15 00:00:00', '2023-06-15 00:00:00', '2026-06-15 00:00:00'],
                ['2026-09-15 00:00:00', '2023-06-15 00:00:00', '2026-06-15 00:00:00'],
            ]
        );
    }

    private function makeCourtOrder(Client $client): CourtOrder
    {
        return new CourtOrder('', CourtOrderType::PFA, CourtOrderReportType::OPG102, CourtOrderKind::Single, new \DateTime(), $client);
    }
}
