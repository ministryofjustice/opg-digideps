<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\Deputy\DeputyType;

class RegistrationFixtures extends AbstractDataFixture
{
    public function doLoad(FixtureService $fixtureService): void
    {
        for ($i = 0; $i < 3; ++$i) {
            foreach (CourtOrderReportType::cases() as $reportType) {
                $fixtureService->instantiateScenario($this->makeScenario($reportType, DeputyType::LAY));
            }
            foreach (CourtOrderReportType::cases() as $reportType) {
                $fixtureService->instantiateScenario($this->makeScenario($reportType, DeputyType::PRO));
            }
            foreach (CourtOrderReportType::cases() as $reportType) {
                $fixtureService->instantiateScenario($this->makeScenario($reportType, DeputyType::PA));
            }
        }
        for ($i = 0; $i < 3; ++$i) {
            ['persons' => $persons] = $fixtureService->instantiateScenario($this->makeScenario(CourtOrderReportType::OPG102, DeputyType::LAY));
            ['persons' => $persons] = $fixtureService->instantiateScenario($this->makeScenario(CourtOrderReportType::OPG103, DeputyType::LAY), $persons);
            $fixtureService->instantiateScenario($this->makeScenario(CourtOrderReportType::OPG104, DeputyType::LAY), $persons);
        }
        for ($i = 0; $i < 3; ++$i) {
            $fixtureService->instantiateScenario(new Scenario(
                new CourtOrderDescriptor(
                    new DeputySet(
                        new DeputyDescriptor('d1', DeputyType::LAY, hasLogin: false),
                        new DeputyDescriptor('d2', DeputyType::LAY, hasLogin: false),
                    ),
                    CourtOrderReportType::OPG102,
                    single: false,
                )
            ));
            $fixtureService->instantiateScenario(new Scenario(
                new CourtOrderDescriptor(
                    new DeputySet(
                        new DeputyDescriptor('d1', DeputyType::LAY, hasLogin: false),
                        new DeputyDescriptor('d2', DeputyType::LAY, hasLogin: false),
                    ),
                    CourtOrderReportType::OPG102,
                    single: false,
                    siblingDeputySet: new DeputySet(
                        new DeputyDescriptor('d1', DeputyType::LAY, hasLogin: false),
                        new DeputyDescriptor('d3', DeputyType::LAY, hasLogin: false),
                    ),
                )
            ));
        }
    }

    private function makeScenario(CourtOrderReportType $reportType, DeputyType $deputyType): Scenario
    {
        return new Scenario(new CourtOrderDescriptor(new DeputySet(
            new DeputyDescriptor('deputy', $deputyType, hasLogin: false)
        ), $reportType));
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return !in_array($workspace, ['production', 'preproduction']) && in_array($environment, ['dev', 'local']);
    }
}
