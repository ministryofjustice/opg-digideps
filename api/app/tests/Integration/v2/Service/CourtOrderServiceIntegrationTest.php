<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Service;

use App\Entity\CourtOrderDeputy;
use App\Repository\CourtOrderDeputyRepository;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\Tests\Integration\Fixtures;
use App\v2\Service\CourtOrderService;

class CourtOrderServiceIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static CourtOrderDeputyRepository $courtOrderDeputyRepository;
    private static CourtOrderService $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var CourtOrderDeputyRepository $repo */
        $repo = self::$entityManager->getRepository(CourtOrderDeputy::class);
        self::$courtOrderDeputyRepository = $repo;

        self::$sut = self::$container->get(CourtOrderService::class);
    }

    public function testAssociateDeputyWithCourtOrderDuplicate(): void
    {
        $deputy = self::$fixtures->createDeputy(['setDeputyUid' => '123456']);
        $courtOrder = self::$fixtures->createCourtOrder('123567', 'pfa', 'ACTIVE');
        $deputy->associateWithCourtOrder($courtOrder);
        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        $success = self::$sut->associateCourtOrderWithDeputy($deputy, $courtOrder);
        $this->assertFalse($success);
    }

    public function testAssociateDeputyWithCourtOrderSuccess(): void
    {
        $deputy = self::$fixtures->createDeputy(['setDeputyUid' => 'x123456']);
        $courtOrder = self::$fixtures->createCourtOrder('x123567', 'pfa', 'ACTIVE');
        self::$entityManager->persist($courtOrder);
        self::$entityManager->flush();

        $success = self::$sut->associateCourtOrderWithDeputy($deputy, $courtOrder);
        $this->assertTrue($success);

        $rel = self::$courtOrderDeputyRepository->findOneBy(['courtOrder' => $courtOrder, 'deputy' => $deputy]);
        $this->assertNotNull($rel);
        $this->assertTrue($rel->isActive());
    }
}
