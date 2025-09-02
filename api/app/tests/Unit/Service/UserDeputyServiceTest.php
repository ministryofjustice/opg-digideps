<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Deputy;
use App\Entity\PreRegistration;
use App\Repository\PreRegistrationRepository;
use App\Service\DeputyService;
use App\Service\UserDeputyService;
use PHPUnit\Framework\TestCase;

class UserDeputyServiceTest extends TestCase
{
    private PreRegistrationRepository $mockPreRegistrationRepository;
    private DeputyService $mockDeputyService;
    private UserDeputyService $sut;

    public function setUp(): void
    {
        $this->mockPreRegistrationRepository = self::createMock(PreRegistrationRepository::class);
        $this->mockDeputyService = self::createMock(DeputyService::class);

        $this->sut = new UserDeputyService(
            $this->mockPreRegistrationRepository,
            $this->mockDeputyService,
        );
    }

    public function testAddMissingUserDeputyAssociations(): void
    {
        $expected = 0;

        $mockPreReg = self::createMock(PreRegistration::class);
        $preRegs = [$mockPreReg];

        $mockDeputy = self::createMock(Deputy::class);

        // expect pre-reg repo to provide list of rows where deputy UID is not in the deputy table
        $this->mockPreRegistrationRepository->expects($this->once())
            ->method('findWithoutDeputies')
            ->willReturn($preRegs);

        // expect deputy repo to add deputies for each pre-reg row found
        $this->mockDeputyService->expects($this->once())
            ->method('createDeputyFromPreRegistration')
            ->with($mockPreReg)
            ->willReturn($mockDeputy);

        // expect <repo or service> to provide list of users whose deputy UID is in pre_registration but who have no deputy

        // expect deputies to be associated with users

        $actual = $this->sut->addMissingUserDeputies();

        self::assertEquals($expected, $actual);
    }
}
