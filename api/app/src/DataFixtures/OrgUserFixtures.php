<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Backend\Fixture\UserType;
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
        $team = new DeputySet(
            new DeputyDescriptor("{$deputyTypeLabel}-{$label}-named", $deputyType),
            new DeputyDescriptor("{$deputyTypeLabel}-{$label}-admin-1", $deputyType, UserType::OrgAdmin),
            new DeputyDescriptor("{$deputyTypeLabel}-{$label}-admin-2", $deputyType, UserType::OrgAdmin),
            new DeputyDescriptor("{$deputyTypeLabel}-{$label}-member-1", $deputyType, UserType::OrgTeamMember),
            new DeputyDescriptor("{$deputyTypeLabel}-{$label}-member-2", $deputyType, UserType::OrgTeamMember),
        );
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
        return $workspace === 'olivierd2797' && in_array($environment, ['dev', 'local']);
    }
}
