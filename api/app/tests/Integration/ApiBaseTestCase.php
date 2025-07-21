<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiBaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel(['environment' => 'test']);

        // static::getContainer() is subtly different from $kernel->getContainer();
        // the latter doesn't always work, depending on the test, but the former does
        $this->container = static::getContainer();

        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->purgeDatabase();
        $this->entityManager->close();
        unset($this->entityManager);
    }

    /**
     * @param string[] $excludeTables Tables to exclude from purge
     */
    protected function purgeDatabase(array $excludeTables = ['dd_user']): void
    {
        $purger = new ORMPurger($this->entityManager, $excludeTables);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }
}
