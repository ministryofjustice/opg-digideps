<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiBaseTestCase extends KernelTestCase
{
    protected static ?EntityManagerInterface $staticEntityManager;
    protected static ?ContainerInterface $staticContainer;
    protected ContainerInterface $container;
    protected EntityManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = &self::$staticContainer;
        $this->entityManager = &self::$staticEntityManager;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::bootKernel(['environment' => 'test']);

        self::$staticContainer = self::$kernel->getContainer();
        self::$staticEntityManager = self::$staticContainer->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::$staticEntityManager->flush();
        self::$staticEntityManager->clear();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::purgeDatabase();

        self::$staticEntityManager->close();
        self::$staticEntityManager = null;
        self::$staticContainer = null;
    }

    /**
     * @param string[] $excludeTables Tables to exclude from purge
     */
    protected static function purgeDatabase(array $excludeTables = ['dd_user']): void
    {
        $purger = new ORMPurger(self::$staticEntityManager, $excludeTables);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }
}
