<?php

declare(strict_types=1);

namespace App\Tests\Integration\Factory;

use App\Factory\ClientIdFixDataFactory;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\Tests\Integration\Fixtures;
use PHPUnit\Framework\Attributes\DataProvider;

class ClientIdFixDataFactoryTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static ClientIdFixDataFactory $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        self::$sut = new ClientIdFixDataFactory(self::$entityManager);
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
        $courtOrder = self::$fixtures->createCourtOrder($courtOrderUid, 'pfa', 'ACTIVE');
        $courtOrder->setClient($oldClient);

        // report also associated with old inactive client
        $report = self::$fixtures->createReport($oldClient);
        $courtOrder->addReport($report);

        self::$entityManager->persist($report);
        self::$entityManager->persist($courtOrder);
        self::$entityManager->flush();

        // run
        self::$sut->run();

        // assertions

        // check the court order
        self::$entityManager->refresh($courtOrder);

        self::assertEquals(
            $newClient,
            $courtOrder->getClient(),
            'court order should have been updated to new active client ID'
        );

        // check the report
        self::$entityManager->refresh($report);

        self::assertEquals(
            $newClient,
            $report->getClient(),
            'report should have been updated to new active client ID'
        );
    }
}
