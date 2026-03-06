<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Repository\DeputyRepository;
use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;
use App\Service\DeputyService;
use App\Service\UserDeputyService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserDeputyServiceTest extends TestCase
{
    private DeputyService $mockDeputyService;
    private UserRepository $mockUserRepository;
    private DeputyRepository $mockDeputyRepository;
    private LoggerInterface $mockLogger;
    private UserDeputyService $sut;

    public function setUp(): void
    {
        $this->mockDeputyService = self::createMock(DeputyService::class);
        $this->mockUserRepository = self::createMock(UserRepository::class);
        $this->mockDeputyRepository = self::createMock(DeputyRepository::class);
        $this->mockLogger = self::createMock(LoggerInterface::class);

        $this->sut = new UserDeputyService(
            $this->mockDeputyService,
            $this->mockUserRepository,
            $this->mockDeputyRepository,
            $this->mockLogger,
        );
    }

    public function testAddMissingDeputyAssociationsDeputyDoesExistAndDoesNotHaveAnExistingUser(): void
    {
        $deputyUid = 512436785;
        $deputyId = 1;

        $mockDeputy = self::createMock(Deputy::class);
        $mockUser = self::createMock(User::class);

        // expect user repo to provide list of users whose deputy UID is in pre-reg but who have no deputy
        $this->mockUserRepository->expects(self::once())
            ->method('findUsersWithoutDeputies')
            ->willReturnCallback(function () use ($mockUser) {
                yield $mockUser;
            });

        $this->mockDeputyRepository
            ->expects(self::once())->method('getUidToIdMapping')
            ->willReturn(["$deputyUid" => $deputyId]);

        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $this->mockDeputyRepository->expects(self::once())
            ->method('find')
            ->with($deputyId)
            ->willReturn($mockDeputy);

        $mockDeputy->expects(self::once())->method('getUser')->willReturn(null);
        $mockDeputy->expects(self::once())->method('setUser')->with($mockUser);

        $mockUser->expects(self::once())->method('setDeputy')->with($mockDeputy);

        $this->mockUserRepository->expects(self::once())->method('save')->with($mockUser);

        $actual = $this->sut->addMissingUserDeputies();

        self::assertEquals(1, $actual);
    }

    public function testAddMissingDeputyAssociationsDeputyDoesNotExist(): void
    {
        $deputyUid = 512436785;

        $mockDeputy = self::createMock(Deputy::class);

        $mockUser = self::createMock(User::class);
        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);
        $mockUser->expects(self::once())->method('setDeputy')->with($mockDeputy);

        $this->mockUserRepository->expects(self::once())
            ->method('findUsersWithoutDeputies')
            ->willReturnCallback(function () use ($mockUser) {
                yield $mockUser;
            });

        // this returns a deputy UID to ID mapping which does not include the user's deputy UID,
        // so a new deputy will be created
        $this->mockDeputyRepository
            ->expects(self::once())->method('getUidToIdMapping')
            ->willReturn(['53428321' => 1]);

        $this->mockDeputyService->expects(self::once())
            ->method('createDeputyFromUser')
            ->with($mockUser)
            ->willReturn($mockDeputy);

        $this->mockDeputyRepository->expects(self::once())
            ->method('save')
            ->with($mockDeputy);

        $this->mockUserRepository->expects(self::once())
            ->method('save')
            ->with($mockUser);

        $actual = $this->sut->addMissingUserDeputies();

        self::assertEquals(1, $actual);
    }

    public function testAddMissingDeputyAssociationsDeputyDoesExistAndDoesHaveAnExistingUser(): void
    {
        $deputyUid = 512436791;
        $userId = $deputyId = 1;

        $mockDeputy = self::createMock(Deputy::class);
        $mockUser = self::createMock(User::class);
        $mockExistingUser = self::createMock(User::class);

        // expect user repo to provide list of users whose deputy UID is in pre-reg but who have no deputy
        $this->mockUserRepository->expects(self::once())
            ->method('findUsersWithoutDeputies')
            ->willReturnCallback(function () use ($mockUser) {
                yield $mockUser;
            });

        $this->mockDeputyRepository
            ->expects(self::once())->method('getUidToIdMapping')
            ->willReturn(["$deputyUid" => $deputyId]);

        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $this->mockDeputyRepository->expects(self::once())
            ->method('find')
            ->with($deputyId)
            ->willReturn($mockDeputy);

        $mockDeputy->expects(self::once())->method('getUser')->willReturn($mockExistingUser);

        $mockDeputy->expects(self::once())->method('getId')->willReturn($deputyId);
        $mockExistingUser->expects(self::once())->method('getId')->willReturn($userId);

        $this->mockLogger->expects(self::once())->method('error')->with(
            sprintf(
                'Deputy with ID %d already associated with user with ID %d',
                $deputyId,
                $userId
            )
        );

        $actual = $this->sut->addMissingUserDeputies();

        self::assertEquals(0, $actual);
    }
}
