<?php declare(strict_types=1);


namespace Tests;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiBaseTestCase extends WebTestCase
{
    /** @var ContainerInterface|null  */
    protected $container;

    /** @var \Doctrine\ORM\EntityManagerInterface|null */
    protected $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel(['environment' => 'test']);

        $this->container = $kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }
}
