<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\Uploader;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\v2\Assembler\ClientAssembler;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;
use OPG\Digideps\Backend\v2\Registration\Uploader\ClientMatch;
use OPG\Digideps\Backend\v2\Registration\Uploader\LayClientMatcher;
use OPG\Digideps\Backend\v2\Registration\Uploader\LayDeputyshipProcessor;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LayDeputyshipProcessorTest extends TestCase
{
    /*
     * Unable to find the user to associate the client with -> error
     */
    public function testProcessRowNoUserException(): void
    {
        $stubEm = $this->createStub(EntityManagerInterface::class);
        $stubClientAssembler = $this->createStub(ClientAssembler::class);
        $mockClientMatcher = $this->createMock(LayClientMatcher::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $sut = new LayDeputyshipProcessor(
            $stubEm,
            $stubClientAssembler,
            $mockClientMatcher,
            $mockLogger
        );

        // Expectations
        $layDeputyshipDto = new LayDeputyshipDto();
        $layDeputyshipDto->setDeputyUid('11111111');
        $layDeputyshipDto->setCaseNumber('99999999');

        $mockClientMatcher->expects($this->once())
            ->method('matchDto')
            ->willThrowException(new Exception('Error matching Lay Deputy DTO to database'));

        $mockLogger->expects($this->once())
            ->method('warning')
            ->with($this->matchesRegularExpression('/Error matching Lay Deputy DTO to database/'));

        // Test
        $output = $sut->processLayDeputyship($layDeputyshipDto);

        // Assert
        $this->assertEquals([], $output['entityDetails']);
        $this->assertStringContainsString(
            'Error when creating entities for deputyUID 11111111 for case 99999999',
            $output['error']
        );
    }

    // matching client and report -> possible co-deputy, so do nothing
    public function testProcessRowMatchingClientAndReport(): void
    {
        $stubEm = $this->createStub(EntityManagerInterface::class);
        $stubClientAssembler = $this->createStub(ClientAssembler::class);
        $mockClientMatcher = $this->createMock(LayClientMatcher::class);
        $stubLogger = $this->createStub(LoggerInterface::class);

        $sut = new LayDeputyshipProcessor(
            $stubEm,
            $stubClientAssembler,
            $mockClientMatcher,
            $stubLogger
        );

        // Expectations
        $layDeputyshipDto = new LayDeputyshipDto();
        $existingClient = new Client();

        $existingReport = new Report(new CourtOrder(
            '',
            CourtOrderType::PFA,
            CourtOrderReportType::OPG102,
            CourtOrderKind::Single,
            new \DateTime(),
            $existingClient
        ), '102', new \DateTime(), new \DateTime(), false)->setId(1);

        $clientMatch = new ClientMatch(
            client: $existingClient,
            report: $existingReport,
            reportTypeShouldChangeTo: null,
            activeClientExistsForCase: true,
        );

        $mockClientMatcher->expects($this->once())
            ->method('matchDto')
            ->with($layDeputyshipDto)
            ->willReturn($clientMatch);

        // Test
        $output = $sut->processLayDeputyship($layDeputyshipDto);

        // Assert
        $expected = [
            'entityDetails' => [],
            'message' => 'Found a potential co-deputy or dual; will not create new multi-client entities',
            'error' => null,
        ];

        $this->assertEquals($expected, $output);
    }

    // no matching client -> create a new client and report
    public function testProcessRowNoMatchingClient(): void
    {
        $mockEm = $this->createMock(EntityManagerInterface::class);
        $mockClientAssembler = $this->createMock(ClientAssembler::class);
        $mockClientMatcher = $this->createMock(LayClientMatcher::class);
        $stubLogger = $this->createStub(LoggerInterface::class);

        $mockUserRepository = $this->createMock(UserRepository::class);

        $sut = new LayDeputyshipProcessor(
            $mockEm,
            $mockClientAssembler,
            $mockClientMatcher,
            $stubLogger
        );

        // Expectations
        $orderDate = new \DateTime('2025-02-14');

        $layDeputyshipDto = new LayDeputyshipDto();
        $layDeputyshipDto->setDeputyUid('222222222')
            ->setCaseNumber('88888888')
            ->setOrderType('hw')
            ->setTypeOfReport('OPG102')
            ->setOrderDate($orderDate);

        $user = new User('', '', '');
        $user->setDeputyUid(222222222);

        $mockEm->expects($this->once())->method('getRepository')->willReturn($mockUserRepository);

        $mockUserRepository->expects($this->once())
            ->method('findPrimaryUserByDeputyUid')
            ->with('222222222')
            ->willReturn($user);

        $clientMatch = new ClientMatch(
            client: null,
            report: null,
            reportTypeShouldChangeTo: null,
            activeClientExistsForCase: false,
        );

        $mockClientMatcher->expects($this->once())
            ->method('matchDto')
            ->with($layDeputyshipDto)
            ->willReturn($clientMatch);

        $mockClient = $this->createMock(Client::class);

        $mockClientAssembler->expects($this->once())
            ->method('assembleFromLayDeputyshipDto')
            ->with($layDeputyshipDto)
            ->willReturn($mockClient);

        $mockClient->expects($this->once())->method('addUser')->with($user);

        $mockEm->expects($this->once())->method('persist');
        $mockEm->expects($this->once())->method('flush');
        $mockEm->expects($this->once())->method('commit');
        $mockEm->expects($this->once())->method('clear');

        $mockClient->expects($this->once())->method('getUsers')->willReturn(new ArrayCollection([$user]));
        $mockClient->expects($this->once())->method('getId')->willReturn(33333333);
        $mockClient->expects($this->once())->method('getCaseNumber')->willReturn('88888888');

        // Test
        $output = $sut->processLayDeputyship($layDeputyshipDto);

        // Assert
        $expected = [
            'clientId' => 33333333,
            'clientCaseNumber' => '88888888',
            'clientDeputyUids' => [222222222],
            'dto.caseNumber' => '88888888',
            'dto.deputyUid' => '222222222',
            'dto.orderType' => 'hw',
            'dto.typeOfReport' => 'OPG102',
            'dto.orderDate' => $orderDate,
        ];

        $this->assertEquals(null, $output['error']);
        $this->assertEquals($expected, $output['entityDetails']);
    }
}
