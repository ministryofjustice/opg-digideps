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
use App\v2\DTO\InviteeDto;
use App\v2\Service\CourtOrderInviteService;
use App\v2\Service\CourtOrderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CourtOrderInviteServiceTest extends TestCase
{
    private PreRegistrationRepository&MockObject $mockPreRegistrationRepository;
    private CourtOrderService&MockObject $mockCourtOrderService;
    private UserService&MockObject $mockUserService;
    private DeputyService&MockObject $mockDeputyService;
    private EntityManagerInterface&MockObject $mockEntityManager;
    private CourtOrderInviteService $sut;

    public function setUp(): void
    {
        $this->mockPreRegistrationRepository = self::createMock(PreRegistrationRepository::class);
        $this->mockCourtOrderService = self::createMock(CourtOrderService::class);
        $this->mockUserService = self::createMock(UserService::class);
        $this->mockDeputyService = self::createMock(DeputyService::class);
        $this->mockEntityManager = self::createMock(EntityManagerInterface::class);

        $this->sut = new CourtOrderInviteService(
            $this->mockPreRegistrationRepository,
            $this->mockCourtOrderService,
            $this->mockUserService,
            $this->mockDeputyService,
            $this->mockEntityManager,
        );
    }

    public function testInviteLayDeputyNotLay(): void
    {
        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope', User::ROLE_ORG_TEAM_MEMBER);

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $invited = $this->sut->inviteLayDeputy('1122334455', $invitingUser, $inviteeDTO);

        self::assertFalse($invited->success);
        self::assertStringContainsString('invited deputy is not a Lay deputy', $invited->message);
    }

    public function testInviteLayDeputyNoAccessToCourtOrder(): void
    {
        $courtOrderUid = '91853764';

        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope');

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $invitingUser)
            ->willReturn(null);

        $invited = $this->sut->inviteLayDeputy($courtOrderUid, $invitingUser, $inviteeDTO);

        self::assertFalse($invited->success);
        self::assertStringContainsString('either court order does not exist, or inviting deputy cannot access it', $invited->message);
    }

    public function testInviteLayDeputyCourtOrderClientHasNoCaseNumber(): void
    {
        $courtOrderUid = '91853764';

        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope');

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $invitingUser)
            ->willReturn($mockCourtOrder);

        $mockCourtOrder->expects(self::once())->method('getClient')->willReturn($mockClient);
        $mockClient->expects(self::once())->method('getCaseNumber')->willReturn(null);

        $invited = $this->sut->inviteLayDeputy($courtOrderUid, $invitingUser, $inviteeDTO);

        self::assertFalse($invited->success);
        self::assertStringContainsString('could not find case number for court order', $invited->message);
    }

    public function testInviteLayDeputyMissingPreRegRecord(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';

        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope');

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $invitingUser)
            ->willReturn($mockCourtOrder);

        $mockCourtOrder->expects(self::once())->method('getClient')->willReturn($mockClient);
        $mockClient->expects(self::once())->method('getCaseNumber')->willReturn($caseNumber);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findInvitedLayDeputy')
            ->with($inviteeDTO, $caseNumber)
            ->willReturn(null);

        $result = $this->sut->inviteLayDeputy($courtOrderUid, $invitingUser, $inviteeDTO);

        self::assertFalse($result->success);
        self::assertStringContainsString('no record in pre-reg table', $result->message);
    }

    public function testInviteLayDeputyNoDeputyUidInPreRegRecord(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';

        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope');

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);
        $mockPreRegistration = self::createMock(PreRegistration::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $invitingUser)
            ->willReturn($mockCourtOrder);

        $mockCourtOrder->expects(self::once())->method('getClient')->willReturn($mockClient);
        $mockClient->expects(self::once())->method('getCaseNumber')->willReturn($caseNumber);

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findInvitedLayDeputy')
            ->with($inviteeDTO, $caseNumber)
            ->willReturn($mockPreRegistration);

        $mockPreRegistration->expects(self::once())->method('getDeputyUid')->willReturn('');

        $result = $this->sut->inviteLayDeputy('91853764', $invitingUser, $inviteeDTO);

        self::assertFalse($result->success);
        self::assertStringContainsString('empty deputy UID in pre-reg table', $result->message);
    }

    public function testInviteLayDeputyDatabaseFail(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';
        $deputyUid = '12345678';

        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope');

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);
        $mockPreregistration = self::createMock(PreRegistration::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $invitingUser)
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
            ->with($courtOrderUid, $invitingUser)
            ->willReturn($mockCourtOrder);

        $this->mockEntityManager->expects(self::once())->method('beginTransaction');
        $this->mockEntityManager->expects(self::once())->method('rollback');

        $this->mockUserService->expects(self::once())
            ->method('getOrAddUser')
            ->with($inviteeDTO, $invitingUser)
            ->willThrowException(new ORMException('something bad happened on the way to the database'));

        $result = $this->sut->inviteLayDeputy($courtOrderUid, $invitingUser, $inviteeDTO);

        self::assertFalse($result->success);
        self::assertStringContainsString('unexpected error inserting data', $result->message);
    }

    public function testInviteLayDeputy(): void
    {
        $courtOrderUid = '91853764';
        $caseNumber = '1245674332';
        $deputyUid = '12345678';

        $inviteeDTO = new InviteeDto('foo@bar.com', 'Herbert', 'Glope');

        $invitingUser = self::createMock(User::class);
        $invitingUser->expects(self::once())->method('getId')->willReturn(1);

        $mockCourtOrder = self::createMock(CourtOrder::class);
        $mockClient = self::createMock(Client::class);
        $mockPreregistration = self::createMock(PreRegistration::class);
        $mockDeputy = self::createMock(Deputy::class);
        $mockInvitedUser = self::createMock(User::class);

        $this->mockCourtOrderService->expects(self::once())
            ->method('getByUidAsUser')
            ->with($courtOrderUid, $invitingUser)
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
            ->with($courtOrderUid, $invitingUser)
            ->willReturn($mockCourtOrder);

        $this->mockEntityManager->expects(self::once())->method('beginTransaction');
        $this->mockEntityManager->expects(self::once())->method('commit');

        $this->mockUserService->expects(self::once())
            ->method('getOrAddUser')
            ->with($inviteeDTO, $invitingUser)
            ->willReturn($mockInvitedUser);

        $this->mockDeputyService->expects(self::once())
            ->method('getOrAddDeputy')
            ->with(self::isInstanceOf(Deputy::class), $mockInvitedUser)
            ->willReturn($mockDeputy);

        $this->mockCourtOrderService->expects(self::once())
            ->method('associateDeputyWithCourtOrder')
            ->with($mockDeputy, $mockCourtOrder, true);

        $result = $this->sut->inviteLayDeputy($courtOrderUid, $invitingUser, $inviteeDTO);

        self::assertTrue($result->success);
    }
}
