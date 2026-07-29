<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Backend\Fixture\UserType;
use OPG\Digideps\Common\Deputy\DeputyType;

class SmokeTestUserFixtures extends AbstractDataFixture
{
    public function doLoad(FixtureService $fixtureService): void
    {
        $adminUser = $fixtureService->instantiateOnlyUser(UserType::Admin, DeputyType::LAY)
            ->setEmail('smoketestadmin@smoketest.com')
            ->setFirstname('SmokeyJoe')
            ->setLastname('SmokeTest');
        ['client' => $client, 'persons' => ['users' => ['lay1' => $deputyUser]]] = $fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        $client->setCaseNumber('9999999t');
        $deputyUser->setEmail('smoketestuser@smoketest.com')->setRegistrationDate(new \DateTime());

        $fixtureService->persist($adminUser);
        $fixtureService->persist($client);
        $fixtureService->persist($deputyUser);
        $fixtureService->flush();
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return !in_array($workspace, ['production', 'preproduction']) && in_array($environment, ['dev', 'local']);
    }
}
