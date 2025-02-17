<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\UserRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Registration\Assembler\SiriusToLayDeputyshipDtoAssembler;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\Uploader\ClientMatch;
use App\v2\Registration\Uploader\LayClientMatcher;
use App\v2\Registration\Uploader\LayDeputyshipProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LayDeputyshipProcessorTest extends TestCase
{
    private EntityManagerInterface $mockEm;
    private SiriusToLayDeputyshipDtoAssembler $mockLayDeputyAssembler;
    private ClientAssembler $mockClientAssembler;
    private LoggerInterface $mockLogger;
    private LayClientMatcher $mockClientMatcher;
    private UserRepository $mockUserRepository;
    private LayDeputyshipProcessor $sut;

    public function setUp(): void
    {
        $this->mockEm = $this->createMock(EntityManagerInterface::class);
        $this->mockClientAssembler = $this->createMock(ClientAssembler::class);
        $this->mockClientMatcher = $this->createMock(LayClientMatcher::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->mockUserRepository = $this->createMock(UserRepository::class);

        $this->sut = new LayDeputyshipProcessor(
            $this->mockEm,
            $this->mockClientAssembler,
            $this->mockClientMatcher,
            $this->mockLogger
        );
    }

    /*
     * Unable to find the user to associate the client with -> error
     */
    public function testProcessRowNoUserException()
    {
        // Expectations
        $layDeputyshipDto = new LayDeputyshipDto();
        $layDeputyshipDto->setDeputyUid('111111111')
            ->setCaseNumber('99999999');

        $this->mockEm->expects($this->once())->method('beginTransaction');
        $this->mockEm->expects($this->once())->method('getRepository')->willReturn($this->mockUserRepository);

        $this->mockUserRepository->expects($this->once())
            ->method('findPrimaryUserByDeputyUid')
            ->with('111111111')
            ->willThrowException(new NoResultException());

        // Test
        $output = $this->sut->processLayDeputyship($layDeputyshipDto);

        // Assert
        $this->assertEquals([], $output['entityDetails']);
        $this->assertStringContainsString(
            'Error when creating entities for deputyUID 111111111 for case 99999999',
            $output['error']
        );
    }

    public function testProcessRowMatchingClientAndReport()
    {
        // Expectations
        $orderDate = new \DateTime('2025-02-14');

        $layDeputyshipDto = new LayDeputyshipDto();
        $layDeputyshipDto->setDeputyUid('222222222')
            ->setCaseNumber('88888888')
            ->setOrderType('pfa')
            ->setTypeOfReport('OPG102')
            ->setOrderDate($orderDate);

        $user = new User();
        $user->setDeputyUid(222222222);

        $existingClient = $this->createMock(Client::class);

        $mockReportClass = $this->createPartialMock(Report::class, methods: ['getId']);
        $existingReport = new $mockReportClass($existingClient, '102', new \DateTime(), new \DateTime(), false);
        $existingReport->expects($this->once())->method('getId')->willReturn(1);

        $this->mockEm->expects($this->once())->method('beginTransaction');
        $this->mockEm->expects($this->once())->method('getRepository')->willReturn($this->mockUserRepository);

        $this->mockUserRepository->expects($this->once())
            ->method('findPrimaryUserByDeputyUid')
            ->with('222222222')
            ->willReturn($user);

        $clientMatch = new ClientMatch(
            client: $existingClient,
            report: $existingReport,
            reportTypeWasChangedFrom: null,
        );

        $this->mockClientMatcher->expects($this->once())
            ->method('matchDto')
            ->with($layDeputyshipDto)
            ->willReturn($clientMatch);

        $existingClient->expects($this->once())
            ->method('addUser')
            ->with($user);

        $this->mockEm->expects($this->exactly(2))->method('persist');
        $this->mockEm->expects($this->once())->method('flush');
        $this->mockEm->expects($this->once())->method('commit');
        $this->mockEm->expects($this->once())->method('clear');

        $existingClient->expects($this->once())->method('getUsers')->willReturn([$user]);
        $existingClient->expects($this->once())->method('getId')->willReturn(33333333);
        $existingClient->expects($this->once())->method('getCaseNumber')->willReturn('88888888');

        // Test
        $output = $this->sut->processLayDeputyship($layDeputyshipDto);

        // Assert
        $expected = [
            'isNewClient' => false,
            'clientId' => 33333333,
            'clientCaseNumber' => '88888888',
            'clientDeputyUids' => [222222222],
            'isNewReport' => false,
            'reportId' => 1,
            'reportType' => '102',
            'reportTypeWasChangedFrom' => null,
            'dto.caseNumber' => '88888888',
            'dto.deputyUid' => '222222222',
            'dto.orderType' => 'pfa',
            'dto.typeOfReport' => 'OPG102',
            'dto.orderDate' => $orderDate,
        ];

        $this->assertEquals(null, $output['error']);
        $this->assertEquals($expected, $output['entityDetails']);
    }

    public function testProcessRowNoMatchingClient()
    {
        // Expectations
        $orderDate = new \DateTime('2025-02-14');

        $layDeputyshipDto = new LayDeputyshipDto();
        $layDeputyshipDto->setDeputyUid('222222222')
            ->setCaseNumber('88888888')
            ->setOrderType('hw')
            ->setTypeOfReport('OPG104')
            ->setOrderDate($orderDate);

        $user = new User();
        $user->setDeputyUid(222222222);

        $this->mockEm->expects($this->once())->method('beginTransaction');
        $this->mockEm->expects($this->once())->method('getRepository')->willReturn($this->mockUserRepository);

        $this->mockUserRepository->expects($this->once())
            ->method('findPrimaryUserByDeputyUid')
            ->with('222222222')
            ->willReturn($user);

        $clientMatch = new ClientMatch(
            client: null,
            report: null,
            reportTypeWasChangedFrom: null,
        );

        $this->mockClientMatcher->expects($this->once())
            ->method('matchDto')
            ->with($layDeputyshipDto)
            ->willReturn($clientMatch);

        $mockClient = $this->createMock(Client::class);

        $this->mockClientAssembler->expects($this->once())
            ->method('assembleFromLayDeputyshipDto')
            ->with($layDeputyshipDto)
            ->willReturn($mockClient);

        $mockClient->expects($this->once())->method('addUser')->with($user);

        $this->mockEm->expects($this->exactly(2))->method('persist');
        $this->mockEm->expects($this->once())->method('flush');
        $this->mockEm->expects($this->once())->method('commit');
        $this->mockEm->expects($this->once())->method('clear');

        $mockClient->expects($this->once())->method('getUsers')->willReturn([$user]);
        $mockClient->expects($this->once())->method('getId')->willReturn(33333333);
        $mockClient->expects($this->once())->method('getCaseNumber')->willReturn('88888888');

        // Test
        $output = $this->sut->processLayDeputyship($layDeputyshipDto);

        // Assert
        $expected = [
            'isNewClient' => true,
            'clientId' => 33333333,
            'clientCaseNumber' => '88888888',
            'clientDeputyUids' => [222222222],
            'isNewReport' => true,
            'reportId' => null, // in reality this will be a database ID, but we currently can't mock the created report
            'reportType' => '104',
            'reportTypeWasChangedFrom' => null,
            'dto.caseNumber' => '88888888',
            'dto.deputyUid' => '222222222',
            'dto.orderType' => 'hw',
            'dto.typeOfReport' => 'OPG104',
            'dto.orderDate' => $orderDate,
        ];

        $this->assertEquals(null, $output['error']);
        $this->assertEquals($expected, $output['entityDetails']);
    }
}
