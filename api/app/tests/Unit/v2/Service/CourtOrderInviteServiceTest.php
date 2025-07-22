<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
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

    public function testInviteMissingPreRegRecord(): void
    {
        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->willReturn(null);

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('not found in pre-reg table'));

        $invited = $this->sut->invite('91853764', $user, $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInviteNoDeputyUidInPreRegRecord(): void
    {
        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $deputy = self::createMock(Deputy::class);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->willReturn($deputy);

        $deputy->expects(self::once())->method('getDeputyUid')->willReturn('');

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('has empty deputy UID in pre-reg table'));

        $invited = $this->sut->invite('91853764', $user, $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInviteNoAccessToCourtOrder(): void
    {
        $courtOrderUid = '91853764';
        $deputyUid = '12345678';

        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $deputy = self::createMock(Deputy::class);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->willReturn($deputy);

        $deputy->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn(null);

        $this->mockLogger->expects(self::once())
            ->method('error')
            ->with($this->stringContains('either court order does not exist, or inviting deputy cannot access it'));

        $invited = $this->sut->invite($courtOrderUid, $user, $inviteeDTO);

        self::assertFalse($invited);
    }

    public function testInvite(): void
    {
        $courtOrderUid = '91853764';
        $deputyUid = '12345678';

        $inviteeDTO = new InviteeDTO('foo@bar.com', 'Herbert', 'Glope');
        $user = new User();

        $mockDeputy1 = self::createMock(Deputy::class);
        $mockDeputy2 = self::createMock(Deputy::class);
        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockInvitedUser = self::createMock(User::class);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->willReturn($mockDeputy1);

        $mockDeputy1->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $user)
            ->willReturn($mockCourtOrder);

        $this->mockUserService->expects(self::once())
            ->method('getOrAddUser')
            ->with($inviteeDTO, $user)
            ->willReturn($mockInvitedUser);

        $this->mockDeputyService->expects(self::once())
            ->method('addDeputy')
            ->with(self::isInstanceOf(Deputy::class), $mockInvitedUser)
            ->willReturn($mockDeputy2);

        $this->mockCourtOrderService->expects(self::once())
            ->method('associateDeputyWithCourtOrder')
            ->with($mockDeputy2, $mockCourtOrder, true);

        $this->mockLogger->expects(self::never())->method('error');

        $invited = $this->sut->invite($courtOrderUid, $user, $inviteeDTO);

        self::assertTrue($invited);
    }
}
