<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

/**
 * Trait for tests which extend KernelTestCase.
 *
 * This can be used with test classes which need the entity manager to be reset per test.
 */
trait ApiTestTrait
{
    protected static ?EntityManager $entityManager;
    protected static ?ContainerInterface $container;

    // should be called in the setUp() or setUpBeforeClass() method of the KernelTestCase subclass using this trait
    public static function configureTest(): void
    {
        self::bootKernel(['environment' => 'test']);
        self::$container = self::$kernel->getContainer();
        self::$entityManager = self::$container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::$entityManager->flush();
        self::$entityManager->clear();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::purgeDatabase();

        self::$entityManager->close();
        self::$entityManager = null;
        self::$container = null;
    }

    /**
     * @param string[] $excludeTables Tables to exclude from purge
     */
    protected static function purgeDatabase(array $excludeTables = ['dd_user']): void
    {
        $purger = new ORMPurger(self::$entityManager, $excludeTables);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }
}
