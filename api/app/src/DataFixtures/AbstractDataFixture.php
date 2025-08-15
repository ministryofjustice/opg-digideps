<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Provides support for environment specific fixtures.
 *
 * This abstract class is used to only allow loading in specific environments.
 * The environments the data fixture will be loaded in is determined by the list of environment names
 * returned by `getEnvironments()`.
 *
 * > The fixture will still be shown as having been loaded by the Doctrine
 * > command, `doctrine:fixtures:load`, despite not having been actually
 * > loaded.
 */
abstract class AbstractDataFixture implements FixtureInterface
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function load(ObjectManager $manager): void
    {
        if (in_array($this->kernel->getEnvironment(), $this->getEnvironments())) {
            $this->doLoad($manager);
        }
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
