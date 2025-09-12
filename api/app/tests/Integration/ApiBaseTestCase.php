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

    /*
     * This is a work-around for tests where the setUpBeforeClass() does not correctly associate
     * static variables so they are accessible in the test; by booting the kernel per test,
     * and resetting the static member variables, the associations are correct. I'm not sure why this is
     * required when we already do the same in setUpBeforeClass().
     *
     * Note that because these statics are assigned by reference to $this->container and $this->entityManager, changing
     * them here also updates those instance variable references.
     */
    protected static function setUpPerTestWorkAround(): void
    {
        self::bootKernel(['environment' => 'test']);

        self::$staticContainer = self::$kernel->getContainer();
        self::$staticEntityManager = self::$staticContainer->get('doctrine')->getManager();
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
