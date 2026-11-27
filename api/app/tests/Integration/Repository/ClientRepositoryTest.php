<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Repository;

use Doctrine\ORM\EntityManager;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\v2\Assembler\ClientAssembler;
use OPG\Digideps\Backend\v2\Transformer\ClientTransformer;
use OPG\Digideps\Backend\v2\Transformer\DeputyTransformer;
use OPG\Digideps\Backend\v2\Transformer\ReportTransformer;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class ClientRepositoryTest extends ApiIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel(['environment' => 'test', 'debug' => true]);
        self::$container = $kernel->getContainer();
        $manager = self::$container->get('doctrine.orm.default_entity_manager');

        if (!$manager instanceof EntityManager) {
            throw new \LogicException('The default test entity manager is not an ORM entity manager');
        }

        $fixtures = self::$container->get(FixtureService::class);
        if (!$fixtures instanceof FixtureService) {
            throw new \LogicException('The test container is missing the fixture service');
        }

        self::$entityManager = $manager;
        self::$fixtureService = $fixtures;
    }

    #[DataProvider('reportHistories')]
    public function testClientDetailQueryCountIsIndependentOfReportHistory(
        CourtOrderReportType $reportType,
        bool $single,
        bool $dual,
        int $historyLength,
    ): void {
        $deputies = DeputySet::oneLay();
        $siblingDeputies = $dual ? DeputySet::oneLay('lay2') : null;
        $scenario = null;
        foreach (['2020-01-01', '2022-01-01', '2024-01-01'] as $madeDate) {
            $scenario = new Scenario(new CourtOrderDescriptor(
                $deputies,
                $reportType,
                new \DateTimeImmutable($madeDate),
                active: $madeDate === '2024-01-01',
                single: $single,
                siblingDeputySet: $siblingDeputies,
                reportList: ReportList::manyReports(new \DateTimeImmutable($madeDate), $historyLength),
            ), $scenario);
        }
        ['client' => $client] = self::$fixtureService->instantiateScenario($scenario);
        $id = $client->getId();

        /** @var ClientRepository $repository */
        $repository = self::$entityManager->getRepository(Client::class);

        /** @var ClientAssembler $assembler */
        $assembler = self::$container->get(ClientAssembler::class);

        $transformer = new ClientTransformer(new ReportTransformer(), new DeputyTransformer());

        self::$entityManager->clear();
        $existingClient = $repository->findOneBy(['id' => $id]);
        self::assertInstanceOf(Client::class, $existingClient);
        $expected = $transformer->transform($assembler->assembleFromEntity($existingClient));
        self::assertIsArray($expected['possible_report_replacements']);
        self::assertCount($dual ? 2 : 1, $expected['possible_report_replacements']);
        self::$entityManager->clear();

        $queryData = static::getContainer()->get('doctrine.debug_data_holder');
        self::assertInstanceOf(DebugDataHolder::class, $queryData);
        $queryData->reset();
        $loadedClient = $repository->getEntityById($id);
        self::assertNotNull($loadedClient);
        $actual = $transformer->transform($assembler->assembleFromEntity($loadedClient));
        $queries = $queryData->getData();
        self::assertArrayHasKey('default', $queries);
        self::assertIsArray($queries['default']);

        // Users and reports with equal submit dates have no defined relative ordering
        self::assertEqualsCanonicalizing($expected, $actual);

        // Three fetch queries, plus the existing inverse deputy lookup for each user
        self::assertCount($dual ? 5 : 4, $queries['default']);
    }

    public static function reportHistories(): iterable
    {
        foreach ([1, 12] as $historyLength) {
            yield "PFA, $historyLength historical reports" => [CourtOrderReportType::OPG103, true, false, $historyLength];
            yield "HW, $historyLength historical reports" => [CourtOrderReportType::OPG104, true, false, $historyLength];
            yield "hybrid, $historyLength historical reports" => [CourtOrderReportType::OPG103, false, false, $historyLength];
            yield "dual, $historyLength historical reports" => [CourtOrderReportType::OPG103, false, true, $historyLength];
        }
    }

    public function testGetEntityByIdIncludesDischargedClients(): void
    {
        ['client' => $client] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        $id = $client->getId();
        $client->setDeletedAt(new \DateTime('2024-01-01'));
        self::$entityManager->flush();
        self::$entityManager->clear();

        /** @var ClientRepository $repository */
        $repository = self::$entityManager->getRepository(Client::class);
        $loadedClient = $repository->getEntityById($id);

        self::assertNotNull($loadedClient);
        self::assertNotNull($loadedClient->getDeletedAt());
        self::assertTrue(self::$entityManager->getFilters()->isEnabled('softdeleteable'));
    }

    public function testGetEntityByIdReturnsNullForMissingClients(): void
    {
        /** @var ClientRepository $repository */
        $repository = self::$entityManager->getRepository(Client::class);

        self::assertNull($repository->getEntityById(0));
        self::assertTrue(self::$entityManager->getFilters()->isEnabled('softdeleteable'));
    }
}
