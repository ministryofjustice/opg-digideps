<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Provides support for environment specific fixtures.
 *
 * This container aware, abstract data fixture is used to only allow loading in
 * specific environments. The environments the data fixture will be loaded in is
 * determined by the list of environment names returned by `getEnvironments()`.
 *
 * > The fixture will still be shown as having been loaded by the Doctrine
 * > command, `doctrine:fixtures:load`, despite not having been actually
 * > loaded.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractDataFixture implements ContainerAwareInterface, FixtureInterface
{
    /**
     * The dependency injection container.
     *
     * @var ContainerInterface
     */
    protected $container;

    public function load(ObjectManager $manager): void
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');

        if (in_array($kernel->getEnvironment(), $this->getEnvironments())) {
            $this->doLoad($manager);
        }
    }

    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Performs the actual fixtures loading.
     *
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     *
     * @param ObjectManager $manager the object manager
     */
    abstract protected function doLoad(ObjectManager $manager);

    /**
     * Returns the environments the fixtures may be loaded in.
     *
     * @return array the name of the environments
     */
    abstract protected function getEnvironments();
}
