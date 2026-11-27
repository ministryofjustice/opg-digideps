<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\DeputyRepository;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\DeputyService;
use OPG\Digideps\Backend\Service\UserDeputyService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserDeputyServiceTest extends TestCase
{
    public function testAddMissingDeputyAssociationsDeputyDoesExistAndDoesNotHaveAnExistingUser(): void
    {
        $stubDeputyService = self::createStub(DeputyService::class);
        $mockUserRepository = self::createMock(UserRepository::class);
        $mockDeputyRepository = self::createMock(DeputyRepository::class);
        $stubLogger = self::createStub(LoggerInterface::class);

        $sut = new UserDeputyService(
            $stubDeputyService,
            $mockUserRepository,
            $mockDeputyRepository,
            $stubLogger,
        );

        $deputyUid = 512436785;
        $deputyId = 1;

        $mockDeputy = self::createMock(Deputy::class);
        $mockUser = self::createMock(User::class);

        // expect user repo to provide list of users whose deputy UID is in pre-reg but who have no deputy
        $mockUserRepository->expects(self::once())
            ->method('findUsersWithoutDeputies')
            ->willReturnCallback(function () use ($mockUser) {
                yield $mockUser;
            });

        $mockDeputyRepository
            ->expects(self::once())->method('getUidToIdMapping')
            ->willReturn(["$deputyUid" => $deputyId]);

        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $mockDeputyRepository->expects(self::once())
            ->method('find')
            ->with($deputyId)
            ->willReturn($mockDeputy);

        $mockDeputy->expects(self::once())->method('getUser')->willReturn(null);
        $mockDeputy->expects(self::once())->method('setUser')->with($mockUser);

        $mockUser->expects(self::once())->method('setDeputy')->with($mockDeputy);

        $mockUserRepository->expects(self::once())->method('save')->with($mockUser);

        $actual = $sut->addMissingUserDeputies();

        self::assertEquals(1, $actual);
    }

    public function testAddMissingDeputyAssociationsDeputyDoesNotExist(): void
    {
        $mockDeputyService = self::createMock(DeputyService::class);
        $mockUserRepository = self::createMock(UserRepository::class);
        $mockDeputyRepository = self::createMock(DeputyRepository::class);
        $stubLogger = self::createStub(LoggerInterface::class);

        $sut = new UserDeputyService(
            $mockDeputyService,
            $mockUserRepository,
            $mockDeputyRepository,
            $stubLogger,
        );

        $deputyUid = 512436785;

        $stubDeputy = self::createStub(Deputy::class);

        $mockUser = self::createMock(User::class);
        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);
        $mockUser->expects(self::once())->method('setDeputy')->with($stubDeputy);

        $mockUserRepository->expects(self::once())
            ->method('findUsersWithoutDeputies')
            ->willReturnCallback(function () use ($mockUser) {
                yield $mockUser;
            });

        // this returns a deputy UID to ID mapping which does not include the user's deputy UID,
        // so a new deputy will be created
        $mockDeputyRepository
            ->expects(self::once())->method('getUidToIdMapping')
            ->willReturn(['53428321' => 1]);

        $mockDeputyService->expects(self::once())
            ->method('createDeputyFromUser')
            ->with($mockUser)
            ->willReturn($stubDeputy);

        $mockDeputyRepository->expects(self::once())
            ->method('save')
            ->with($stubDeputy);

        $mockUserRepository->expects(self::once())
            ->method('save')
            ->with($mockUser);

        $actual = $sut->addMissingUserDeputies();

        self::assertEquals(1, $actual);
    }

    public function testAddMissingDeputyAssociationsDeputyDoesExistAndDoesHaveAnExistingUser(): void
    {
        $stubDeputyService = self::createStub(DeputyService::class);
        $mockUserRepository = self::createMock(UserRepository::class);
        $mockDeputyRepository = self::createMock(DeputyRepository::class);
        $mockLogger = self::createMock(LoggerInterface::class);

        $sut = new UserDeputyService(
            $stubDeputyService,
            $mockUserRepository,
            $mockDeputyRepository,
            $mockLogger,
        );

        $deputyUid = 512436791;
        $userId = $deputyId = 1;

        $mockDeputy = self::createMock(Deputy::class);
        $mockUser = self::createMock(User::class);
        $mockExistingUser = self::createMock(User::class);

        // expect user repo to provide list of users whose deputy UID is in pre-reg but who have no deputy
        $mockUserRepository->expects(self::once())
            ->method('findUsersWithoutDeputies')
            ->willReturnCallback(function () use ($mockUser) {
                yield $mockUser;
            });

        $mockDeputyRepository
            ->expects(self::once())->method('getUidToIdMapping')
            ->willReturn(["$deputyUid" => $deputyId]);

        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);

        $mockDeputyRepository->expects(self::once())
            ->method('find')
            ->with($deputyId)
            ->willReturn($mockDeputy);

        $mockDeputy->expects(self::once())->method('getUser')->willReturn($mockExistingUser);

        $mockDeputy->expects(self::once())->method('getId')->willReturn($deputyId);
        $mockExistingUser->expects(self::once())->method('getId')->willReturn($userId);

        $mockLogger->expects(self::once())->method('error')->with(
            sprintf(
                'Deputy with ID %d already associated with user with ID %d',
                $deputyId,
                $userId
            )
        );

        $actual = $sut->addMissingUserDeputies();

        self::assertEquals(0, $actual);
    }
}
