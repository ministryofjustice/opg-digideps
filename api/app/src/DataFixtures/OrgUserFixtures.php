<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\Deputy\DeputyType;

class OrgUserFixtures extends AbstractDataFixture
{
    public function doLoad(FixtureService $fixtureService): void
    {
        foreach (CourtOrderReportType::cases() as $type) {
            foreach ([DeputyType::PA, DeputyType::PRO] as $deputyType) {
                $this->makeOrganisationFixture($type, $deputyType, 10);
            }
        }
    }

    private function makeOrganisationFixture(CourtOrderReportType $type, DeputyType $deputyType, int $count): void
    {
        $deputyTypeLabel = strtolower($deputyType->value);
        $label = strtolower($type->value);
        $team = DeputySet::oneTeam($deputyType, "{$deputyTypeLabel}-{$label}", 2, 2, true);
        $scenario = new Scenario(new CourtOrderDescriptor($team, $type));
        $persons = null;
        $domain = "{$deputyTypeLabel}10{$type->getSuffix()}s.gov.uk";
        $persons = $this->instantiateWithDeterministicLogin(10, $scenario, $domain, $persons);
        if ($type !== CourtOrderReportType::OPG104) {
            $hybridScenario = new Scenario(new CourtOrderDescriptor($team, $type, single: false));
            $this->instantiateWithDeterministicLogin(10, $hybridScenario, $domain, $persons);
        }
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return $workspace === 'training' && in_array($environment, ['dev', 'local']);
    }
}
