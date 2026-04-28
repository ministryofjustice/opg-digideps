<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Factory;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Factory\ClientIdFixDataFactory;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;
use PHPUnit\Framework\Attributes\DataProvider;

class ClientIdFixDataFactoryIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (self::$entityManager === null) {
            throw new \LogicException('Improper initialisation');
        }
        self::$fixtures = new Fixtures(self::$entityManager);
    }

    public static function oldClientFieldsProvider(): array
    {
        return [
            'deleted client' => [
                '129328374',
                '11111111',
                ['setDeletedAt' => new \DateTime()],
            ],

            'archived client' => [
                '129328375',
                '22222222',
                ['setArchivedAt' => new \DateTime()],
            ],
        ];
    }

    #[DataProvider('oldClientFieldsProvider')]
    public function testRun(string $courtOrderUid, string $caseNumber, array $oldClientFields): void
    {
        $entityManager = self::$entityManager !== null ? self::$entityManager : throw new \LogicException('Improper initialisation');

        $sut = new ClientIdFixDataFactory($entityManager);

        $oldClientFields['setCaseNumber'] = $caseNumber;

        $user = self::$fixtures->createUser();

        $oldClient = self::$fixtures->createClient(
            $user,
            $oldClientFields,
        );

        $newClient = self::$fixtures->createClient(
            $user,
            ['setCaseNumber' => $caseNumber],
        );

        $entityManager->persist($user);
        $entityManager->persist($oldClient);
        $entityManager->persist($newClient);
        $entityManager->flush();

        // court order associated with old inactive client
        $courtOrder = self::$fixtures->createCourtOrder($courtOrderUid, CourtOrderType::PFA, CourtOrderKind::Single, 'ACTIVE');
        $courtOrder->setClient($oldClient);

        // report also associated with old inactive client
        $report = self::$fixtures->createReport($oldClient);
        $courtOrder->addReport($report);

        $entityManager->persist($report);
        $entityManager->persist($courtOrder);
        $entityManager->flush();

        // run
        $dataFactoryResult = $sut->run(false);

        // assertions
        self::assertTrue($dataFactoryResult->isSuccessful());

        // check the court order
        $entityManager->refresh($courtOrder);

        self::assertEquals(
            $newClient,
            $courtOrder->getClient(),
            'court order should have been updated to new active client ID'
        );

        // check the report
        $entityManager->refresh($report);

        self::assertEquals(
            $newClient,
            $report->getClient(),
            'report should have been updated to new active client ID'
        );
    }

    #[DataProvider('oldClientFieldsProvider')]
    public function testDryRun(string $courtOrderUid, string $caseNumber, array $oldClientFields): void
    {
        $entityManager = self::$entityManager !== null ? self::$entityManager : throw new \LogicException('Improper initialisation');

        $sut = new ClientIdFixDataFactory($entityManager);

        $oldClientFields['setCaseNumber'] = $caseNumber;

        $user = self::$fixtures->createUser();

        $oldClient = self::$fixtures->createClient(
            $user,
            $oldClientFields,
        );

        $newClient = self::$fixtures->createClient(
            $user,
            ['setCaseNumber' => $caseNumber],
        );

        self::$entityManager->persist($user);
        self::$entityManager->persist($oldClient);
        self::$entityManager->persist($newClient);
        self::$entityManager->flush();

        // court order associated with old inactive client
        $courtOrder = self::$fixtures->createCourtOrder($courtOrderUid, CourtOrderType::PFA, CourtOrderKind::Single, 'ACTIVE');
        $courtOrder->setClient($oldClient);

        // report also associated with old inactive client
        $report = self::$fixtures->createReport($oldClient);
        $courtOrder->addReport($report);

        $entityManager->persist($report);
        $entityManager->persist($courtOrder);
        $entityManager->flush();

        // run
        $dataFactoryResult = $sut->run(true);

        // assertions
        self::assertTrue($dataFactoryResult->isSuccessful());

        // check the court order
        $entityManager->refresh($courtOrder);

        self::assertEquals(
            $oldClient,
            $courtOrder->getClient(),
            'court order should not have been updated to new active client ID'
        );

        // check the report
        $entityManager->refresh($report);

        self::assertEquals(
            $oldClient,
            $report->getClient(),
            'report should not have been updated to new active client ID'
        );
    }
}
