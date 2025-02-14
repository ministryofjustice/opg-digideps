<?php

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\PreRegistrationRepository;
use App\Repository\ReportRepository;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\DTO\LayDeputyshipDtoCollection;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationCreationException;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory;
use App\v2\Registration\Uploader\LayCSVRowProcessor;
use App\v2\Registration\Uploader\LayDeputyshipUploader;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LayDeputyshipUploaderTest extends KernelTestCase
{
    /** @var EntityManager|MockObject */
    protected $em;

    /** @var ReportRepository|MockObject */
    protected $reportRepository;

    /** @var PreRegistrationFactory|MockObject */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    /** @var LayDeputyshipUploader */
    private $sut;

    private LayCSVRowProcessor $rowProcessor;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManager::class);
        $this->reportRepository = $this->createMock(ReportRepository::class);
        $this->factory = $this->createMock(PreRegistrationFactory::class);
        $this->rowProcessor = $this->createMock(LayCSVRowProcessor::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new LayDeputyshipUploader(
            $this->em,
            $this->reportRepository,
            $this->factory,
            $this->rowProcessor,
            $this->logger,
        );
    }

    /**
     * @test
     */
    public function throwsExceptionIfDataSetTooLarge()
    {
        $this->expectException(\RuntimeException::class);
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < LayDeputyshipUploader::MAX_UPLOAD + 1; ++$i) {
            $collection->append(new LayDeputyshipDto());
        }

        $this->sut->upload($collection);
    }

    /**
     * @test
     */
    public function persistsAnEntryForEachValidDeputyship()
    {
        $collection = new LayDeputyshipDtoCollection();

        for ($i = 0; $i < 3; ++$i) {
            $collection->append($this->buildLayDeputyshipDto($i));
        }

        // Assert 3 PreRegistration entities will be created.
        $this->factory
            ->expects($this->exactly(3))
            ->method('createFromDto')
            ->willReturnOnConsecutiveCalls(new PreRegistration([]), new PreRegistration([]), new PreRegistration([]));

        // Assert Report Types will not be updated (not relevant for this test).
        $this->reportRepository
            ->expects($this->once())
            ->method('findAllActiveReportsByCaseNumbersAndRole')
            ->with(['case-0', 'case-1', 'case-2'], User::ROLE_LAY_DEPUTY)
            ->willReturn([]);

        $return = $this->sut->upload($collection);

        $this->assertEquals(3, $return['added']);
        $this->assertCount(0, $return['errors']);
    }

    /**
     * @test
     *
     * @dataProvider reportTypeProvider
     */
    public function updatesReportTypeOfActiveReportsIfRequired(
        string $currentReportType,
        string $preRegistrationNewReportType,
        string $expectedNewReportType,
        bool $isDualCase,
        ?string $deputyUid)
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1));

        $caseType = $isDualCase ? 'DUAL' : 'SINGLE';

        $preRegistration = new PreRegistration([
            'ReportType' => $preRegistrationNewReportType,
            'OrderType' => 'OPG104' === $preRegistrationNewReportType ? 'hw' : 'pfa',
            'Hybrid' => $caseType,
            'DeputyUid' => $deputyUid,
        ]);

        $this->factory
            ->expects($this->once())
            ->method('createFromDto')
            ->willReturnOnConsecutiveCalls($preRegistration);

        // Ensure an existing Client is found with an active Report whose type is different to the new type in the upload.

        $existingClient = $this->createMock(Client::class);
        $existingClient
            ->expects($this->once())
            ->method('getCaseNumber')
            ->willReturn('case-1');

        if ($isDualCase) {
            $deputy = $this->createMock(User::class);
            $deputy
                ->expects($this->once())
                ->method('getDeputyNo')
                ->willReturn('12345678');

            $existingClient
                ->expects($this->once())
                ->method('getUsers')
                ->willReturn([$deputy]);
        }

        // $existingClient = (new Client())->setCaseNumber('case-1');
        $activeReport = new Report($existingClient, $currentReportType, new \DateTime(), new \DateTime(), false);
        $this->reportRepository
            ->expects($this->once())
            ->method('findAllActiveReportsByCaseNumbersAndRole')
            ->with(['case-1'], User::ROLE_LAY_DEPUTY)
            ->willReturn([$activeReport]);

        $return = $this->sut->upload($collection);
        $this->assertEquals(1, $return['added']);
        $this->assertCount(0, $return['errors']);
        $this->assertEquals($expectedNewReportType, $activeReport->getType());
    }

    public function reportTypeProvider()
    {
        return [
            'Changes to 102' => ['103', 'OPG102', '102', false, '12345678'],
            'Changes to 103' => ['102', 'OPG103', '103', false, '12345678'],
            'Changes to 104' => ['102', 'OPG104', '104', false, '12345678'],
            'Dual Case changes to 103' => ['102', 'OPG103', '103', true, '12345678'],
            'Dual Case does not change' => ['102', 'OPG103', '102', true, '87654321'],
            'Dual Case does not change with empty uid' => ['102', 'OPG103', '102', true, ''],
            'Dual Case does not change with concat uids' => ['102', 'OPG103', '102', true, '12345678,87654321'],
            'Dual Case does not change with null uid' => ['102', 'OPG103', '102', true, null],
        ];
    }

    /**
     * @test
     */
    public function ignoresDeputyshipsWithInvalidDeputyshipData()
    {
        $collection = new LayDeputyshipDtoCollection();
        $collection->append($this->buildLayDeputyshipDto(1));

        // Ensure factory will throw an exception
        $this
            ->factory
            ->method('createFromDto')
            ->willThrowException(new PreRegistrationCreationException('Unable to create PreRegistration entity'));

        $this->assertReportTypesWillNotBeUpdated();

        $return = $this->sut->upload($collection);

        $this->assertEquals(0, $return['added']);
        $this->assertCount(1, $return['errors']);
        $this->assertEquals('ERROR IN LINE: Unable to create PreRegistration entity', $return['errors'][0]);
    }

    private function buildLayDeputyshipDto($count): LayDeputyshipDto
    {
        return (new LayDeputyshipDto())
            ->setCaseNumber('case-'.$count)
            ->setDeputyUid('depnum-'.$count);
    }

    private function assertReportTypesWillNotBeUpdated(): void
    {
        $this->reportRepository
            ->expects($this->once())
            ->method('findAllActiveReportsByCaseNumbersAndRole')
            ->with([], User::ROLE_LAY_DEPUTY)
            ->willReturn([]);
    }

    /**
     * @test
     */
    public function testHandleNewMultiClientsNoNewClientsToAdd()
    {
        $mockPreRegistrationRepo = $this->createMock(PreRegistrationRepository::class);

        $mockPreRegistrationRepo->expects($this->once())
            ->method('getNewClientsForExistingDeputiesArray')
            ->willReturn([]);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('No new multi clients to add');

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(PreRegistration::class)
            ->willReturn($mockPreRegistrationRepo);

        $actual = $this->sut->handleNewMultiClients();

        $this->assertEquals(0, $actual['new-clients-found']);
        $this->assertEquals(0, $actual['clients-added']);
        $this->assertEquals([], $actual['errors']);
    }

    /**
     * @test
     */
    public function testHandleNewMultiClients()
    {
        $case1 = ['Case' => '11111111'];
        $case2 = ['Case' => '22222222'];
        $case3 = ['Case' => '33333333'];

        $mockPreRegistrationRepo = $this->createMock(PreRegistrationRepository::class);
        $mockPreRegistrationRepo->expects($this->once())
            ->method('getNewClientsForExistingDeputiesArray')
            ->willReturn([$case1, $case2, $case3]);

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(PreRegistration::class)
            ->willReturn($mockPreRegistrationRepo);

        $this->rowProcessor->expects($this->exactly(3))
            ->method('processRow')
            ->willReturnCallback(function (array $case) use ($case1, $case2, $case3) {
                if ($case === $case1) {
                    return [
                        'entityDetails' => ['clientCaseNumber' => '11111111', 'isNewClient' => false],
                        'error' => null,
                    ];
                }
                if ($case === $case2) {
                    return [
                        'entityDetails' => ['clientCaseNumber' => '22222222', 'isNewClient' => true],
                        'error' => null,
                    ];
                }
                if ($case === $case3) {
                    return [
                        'entityDetails' => [],
                        'error' => 'an error occurred',
                    ];
                }
            });

        $actual = $this->sut->handleNewMultiClients();

        $this->assertEquals(3, $actual['new-clients-found']);
        $this->assertEquals(1, $actual['clients-added']);
        $this->assertEquals(['an error occurred'], $actual['errors']);
    }
}
