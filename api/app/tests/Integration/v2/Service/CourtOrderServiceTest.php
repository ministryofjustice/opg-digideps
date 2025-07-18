<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Service;

use App\Entity\CourtOrderDeputy;
use App\Repository\CourtOrderDeputyRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;
use App\v2\Service\CourtOrderService;

class CourtOrderServiceTest extends ApiBaseTestCase
{
    private Fixtures $fixtures;
    private CourtOrderDeputyRepository $courtOrderDeputyRepository;
    private CourtOrderService $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures($this->entityManager);

        /** @var CourtOrderDeputyRepository $repo */
        $repo = $this->entityManager->getRepository(CourtOrderDeputy::class);
        $this->courtOrderDeputyRepository = $repo;

        $this->sut = $this->getContainer()->get(CourtOrderService::class);
    }

    public function testAssociateDeputyWithCourtOrderNoDeputy(): void
    {
        $success = $this->sut->associateDeputyWithCourtOrder('123456', '123567');
        $this->assertFalse($success);
    }

    public function testAssociateDeputyWithCourtOrderDeputyButNoNoCourtOrder(): void
    {
        $this->fixtures->createDeputy(['setDeputyUid' => '123456']);

        $success = $this->sut->associateDeputyWithCourtOrder('123456', '123567');
        $this->assertFalse($success);
    }

    public function testAssociateDeputyWithCourtOrderDuplicate(): void
    {
        $deputy = $this->fixtures->createDeputy(['setDeputyUid' => '123456']);
        $courtOrder = $this->fixtures->createCourtOrder('123567', 'pfa', 'ACTIVE');
        $deputy->associateWithCourtOrder($courtOrder);
        $this->entityManager->persist($deputy);
        $this->entityManager->flush();

        $success = $this->sut->associateDeputyWithCourtOrder('123456', '123567');
        $this->assertFalse($success);
    }

    public function testAssociateDeputyWithCourtOrderSuccess(): void
    {
        $deputy = $this->fixtures->createDeputy(['setDeputyUid' => 'x123456']);
        $courtOrder = $this->fixtures->createCourtOrder('x123567', 'pfa', 'ACTIVE');
        $this->entityManager->persist($courtOrder);
        $this->entityManager->flush();

        $success = $this->sut->associateDeputyWithCourtOrder('x123456', 'x123567');
        $this->assertTrue($success);

        $rel = $this->courtOrderDeputyRepository->findOneBy(['courtOrder' => $courtOrder, 'deputy' => $deputy]);
        $this->assertNotNull($rel);
        $this->assertTrue($rel->isActive());
    }
}
