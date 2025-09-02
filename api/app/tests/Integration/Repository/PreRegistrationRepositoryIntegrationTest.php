<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\PreRegistration;
use App\Repository\PreRegistrationRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;

class PreRegistrationRepositoryIntegrationTest extends ApiBaseTestCase
{
    private PreRegistrationRepository $sut;
    private Fixtures $fixtures;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures($this->entityManager);

        /** @var PreRegistrationRepository $sut */
        $sut = $this->container->get(PreRegistrationRepository::class);
        $this->sut = $sut;
    }

    public function testFindWithoutDeputies(): void
    {
        // pre-registration row with a deputy UID not in the deputy table
        $preReg1 = new PreRegistration(['DeputyUid' => '165235778']);
        $this->entityManager->persist($preReg1);

        // pre-reg row where a corresponding deputy exists
        $deputy = $this->fixtures->createDeputy(['setDeputyUid' => '94827576']);
        $this->entityManager->persist($deputy);

        $preReg2 = new PreRegistration(['DeputyUid' => '94827576']);
        $this->entityManager->persist($preReg2);

        $this->entityManager->flush();

        $preRegsWithoutDeputies = $this->sut->findWithoutDeputies();

        self::assertCount(1, $preRegsWithoutDeputies);
        self::assertEquals($preReg1->getId(), $preRegsWithoutDeputies[0]->getId());
    }
}
