<?php

namespace OPG\Digideps\Backend\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
 *
 * @phpstan-type Users array<string, User>
 * @phpstan-type Deputies array<string, Deputy>
 * @phpstan-type Organisations array<string, Organisation>
 * @phpstan-type Persons array{users: Users, deputies: Deputies, organisations: Organisations}
 */
abstract class AbstractDataFixture implements FixtureInterface
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly ParameterBagInterface $parameters,
        private readonly FixtureService $fixtureService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $workspace = $this->parameters->get('workspace');
        $environment = $this->kernel->getEnvironment();
        if (is_string($workspace) && $this->shouldLoad($workspace, $environment)) {
            $this->doLoad($this->fixtureService);
        }
    }

    abstract protected function shouldLoad(string $workspace, string $environment): bool;
    abstract protected function doLoad(FixtureService $fixtureService): void;

    /**
     * @param Persons|null $persons
     * @return Persons
     */
    final protected function instantiateWithDeterministicLogin(int $clientCount, Scenario $scenario, string $domain, ?array $persons = null): array
    {
        $persons ??= [
            'users' => [],
            'deputies' => [],
            'organisations' => [],
        ];

        for ($ci = 0; $ci < $clientCount; ++$ci) {
            ['persons' => $persons] = $this->fixtureService->instantiateScenario($scenario, $persons, false);
        }

        foreach ($persons['users'] as $id => $user) {
            $user->setEmail("{$id}@{$domain}");
            $this->fixtureService->persist($user);
        }
        $this->fixtureService->flush();

        return $persons;
    }
}
