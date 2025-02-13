<?php

declare(strict_types=1);

namespace app\tests\Integration;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiBaseTestCase extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManagerInterface|null */
    protected $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel(['environment' => 'test']);

        $container = $kernel->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }
}
