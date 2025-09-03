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

class UserDeputyServiceTest extends TestCase
{
    private PreRegistrationRepository $mockPreRegistrationRepository;
    private DeputyService $mockDeputyService;
    private UserRepository $mockUserRepository;
    private DeputyRepository $mockDeputyRepository;
    private UserDeputyService $sut;

    public function setUp(): void
    {
        $this->mockPreRegistrationRepository = self::createMock(PreRegistrationRepository::class);
        $this->mockDeputyService = self::createMock(DeputyService::class);
        $this->mockUserRepository = self::createMock(UserRepository::class);
        $this->mockDeputyRepository = self::createMock(DeputyRepository::class);

        $this->sut = new UserDeputyService(
            $this->mockPreRegistrationRepository,
            $this->mockDeputyService,
            $this->mockUserRepository,
            $this->mockDeputyRepository,
        );
    }

    public function testAddMissingUserDeputyAssociationsDeputyExists(): void
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

        $mockUser->expects(self::once())->method('setDeputy')->with($mockDeputy);

        $this->mockUserRepository->expects(self::once())->method('save')->with($mockUser);

        $actual = $this->sut->addMissingUserDeputies();

        self::assertEquals(1, $actual);
    }

    public function testAddMissingDeputyAssociationsDeputyDoesNotExist(): void
    {
        $deputyUid = 512436785;
        $deputyEmail = 'some.email@somewhere';

        $mockDeputy = self::createMock(Deputy::class);

        $mockUser = self::createMock(User::class);
        $mockUser->expects(self::once())->method('getDeputyUid')->willReturn($deputyUid);
        $mockUser->expects(self::once())->method('getEmail')->willReturn($deputyEmail);
        $mockUser->expects(self::once())->method('setDeputy')->with($mockDeputy);

        $mockPreRegistration = self::createMock(PreRegistration::class);

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

        $this->mockPreRegistrationRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['deputyUid' => $deputyUid])
            ->willReturn($mockPreRegistration);

        $this->mockDeputyService->expects(self::once())
            ->method('createDeputyFromPreRegistration')
            ->with($mockPreRegistration, ['email' => $deputyEmail])
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
}
