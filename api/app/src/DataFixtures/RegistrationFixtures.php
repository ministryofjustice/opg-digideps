<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;

class RegistrationFixtures extends AbstractDataFixture
{
    public function doLoad(FixtureService $fixtureService): void
    {
        for ($i = 0; $i < 3; ++$i) {
            foreach (CourtOrderReportType::cases() as $reportType) {
                $fixtureService->instantiateScenario($this->makeScenario($reportType));
            }
        }
        for ($i = 0; $i < 3; ++$i) {
            ['persons' => $persons] = $fixtureService->instantiateScenario($this->makeScenario(CourtOrderReportType::OPG102));
            ['persons' => $persons] = $fixtureService->instantiateScenario($this->makeScenario(CourtOrderReportType::OPG103), $persons);
            $fixtureService->instantiateScenario($this->makeScenario(CourtOrderReportType::OPG104), $persons);
        }
    }

    private function makeScenario(CourtOrderReportType $reportType): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor('lay', hasLogin: false)
        ), $reportType));
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return !in_array($workspace, ['production', 'preproduction']) && in_array($environment, ['dev', 'local']);
    }
}
