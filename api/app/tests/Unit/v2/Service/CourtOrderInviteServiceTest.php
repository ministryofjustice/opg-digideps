<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Service;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Repository\PreRegistrationRepository;
use App\Service\DeputyService;
use App\Service\UserService;
use App\v2\DTO\InviteeDTO;
use App\v2\Service\CourtOrderInviteService;
use App\v2\Service\CourtOrderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CourtOrderInviteServiceTest extends TestCase
{
    private PreRegistrationRepository&MockObject $mockPreRegistrationRepository;
    private CourtOrderService&MockObject $mockCourtOrderService;
    private UserService&MockObject $mockUserService;
    private DeputyService&MockObject $mockDeputyService;
    private LoggerInterface&MockObject $mockLogger;
    private CourtOrderInviteService $sut;

    public function setUp(): void
    {
        $this->mockPreRegistrationRepository = self::createMock(PreRegistrationRepository::class);
        $this->mockCourtOrderService = self::createMock(CourtOrderService::class);
        $this->mockUserService = self::createMock(UserService::class);
        $this->mockDeputyService = self::createMock(DeputyService::class);
        $this->mockLogger = self::createMock(LoggerInterface::class);

        $this->sut = new CourtOrderInviteService(
            $this->mockPreRegistrationRepository,
            $this->mockCourtOrderService,
            $this->mockUserService,
            $this->mockDeputyService,
            $this->mockLogger,
        );
    }

    public function testInviteLayDeputyNotLay(): void
    {
        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope', User::ROLE_ORG_TEAM_MEMBER);

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('they are not a Lay deputy'));

        $invited = $this->sut->inviteLayDeputy('1122334455', new User(), $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInviteLayDeputyNoAccessToCourtOrder(): void
    {
        $courtOrderUid = '91853764';

        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn(null);

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('either court order does not exist, or inviting deputy cannot access it'));

        $invited = $this->sut->inviteLayDeputy($courtOrderUid, $user, $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInviteLayDeputyMissingPreRegRecord(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';

        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn($mockCourtOrder);

        $mockCourtOrder->expects(self::once())->method('getClient')->willReturn($mockClient);
        $mockClient->expects(self::once())->method('getCaseNumber')->willReturn($caseNumber);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findInvitedLayDeputy')
            ->with($inviteeDTO, $caseNumber)
            ->willReturn(null);

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('no record in pre-reg table'));

        $invited = $this->sut->inviteLayDeputy($courtOrderUid, $user, $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInviteLayDeputyNoDeputyUidInPreRegRecord(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';

        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);
        $mockPreRegistration = self::createMock(PreRegistration::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn($mockCourtOrder);

        $mockCourtOrder->expects(self::once())->method('getClient')->willReturn($mockClient);
        $mockClient->expects(self::once())->method('getCaseNumber')->willReturn($caseNumber);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findInvitedLayDeputy')
            ->with($inviteeDTO, $caseNumber)
            ->willReturn($mockPreRegistration);

        $mockPreRegistration->expects(self::once())->method('getDeputyUid')->willReturn('');

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('empty deputy UID in pre-reg table'));

        $invited = $this->sut->inviteLayDeputy('91853764', $user, $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInviteLayDeputy(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';
        $deputyUid = '12345678';

        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();
        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);
        $mockPreregistration = self::createMock(PreRegistration::class);
        $mockDeputy = self::createMock(Deputy::class);
        $mockInvitedUser = self::createMock(User::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn($mockCourtOrder);

        $mockCourtOrder->expects(self::once())->method('getClient')->willReturn($mockClient);
        $mockClient->expects(self::once())->method('getCaseNumber')->willReturn($caseNumber);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findInvitedLayDeputy')
            ->with($inviteeDTO, $caseNumber)
            ->willReturn($mockPreregistration);

        $mockPreregistration->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn($mockCourtOrder);

        $this->mockUserService->expects(self::once())
            ->method('getOrAddUser')
            ->with($inviteeDTO, $user)
            ->willReturn($mockInvitedUser);

        $this->mockDeputyService->expects(self::once())
            ->method('getOrAddDeputy')
            ->with(self::isInstanceOf(Deputy::class), $mockInvitedUser)
            ->willReturn($mockDeputy);

        $this->mockCourtOrderService->expects(self::once())
            ->method('associateDeputyWithCourtOrder')
            ->with($mockDeputy, $mockCourtOrder, true);

        $this->mockLogger->expects(self::never())->method('error');

        $invited = $this->sut->inviteLayDeputy($courtOrderUid, $user, $inviteeDTO);

        self::assertTrue($invited);
    }
}
