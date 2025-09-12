<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Service;

use App\Entity\CourtOrderDeputy;
use App\Repository\CourtOrderDeputyRepository;
use App\Tests\Integration\ApiTestTrait;
use App\Tests\Integration\Fixtures;
use App\v2\Service\CourtOrderService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CourtOrderServiceTest extends KernelTestCase
{
    use ApiTestTrait;

    private Fixtures $fixtures;
    private CourtOrderDeputyRepository $courtOrderDeputyRepository;
    private CourtOrderService $sut;

    public function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        $this->fixtures = new Fixtures(self::$entityManager);

        /** @var CourtOrderDeputyRepository $repo */
        $repo = self::$entityManager->getRepository(CourtOrderDeputy::class);
        $this->courtOrderDeputyRepository = $repo;

        $this->sut = self::$container->get(CourtOrderService::class);
    }

    public function testAssociateDeputyWithCourtOrderDuplicate(): void
    {
        $deputy = $this->fixtures->createDeputy(['setDeputyUid' => '123456']);
        $courtOrder = $this->fixtures->createCourtOrder('123567', 'pfa', 'ACTIVE');
        $deputy->associateWithCourtOrder($courtOrder);
        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        $success = $this->sut->associateCourtOrderWithDeputy($deputy, $courtOrder);
        $this->assertFalse($success);
    }

    public function testAssociateDeputyWithCourtOrderSuccess(): void
    {
        $deputy = $this->fixtures->createDeputy(['setDeputyUid' => 'x123456']);
        $courtOrder = $this->fixtures->createCourtOrder('x123567', 'pfa', 'ACTIVE');
        self::$entityManager->persist($courtOrder);
        self::$entityManager->flush();

        $success = $this->sut->associateCourtOrderWithDeputy($deputy, $courtOrder);
        $this->assertTrue($success);

        $rel = $this->courtOrderDeputyRepository->findOneBy(['courtOrder' => $courtOrder, 'deputy' => $deputy]);
        $this->assertNotNull($rel);
        $this->assertTrue($rel->isActive());
    }
}
