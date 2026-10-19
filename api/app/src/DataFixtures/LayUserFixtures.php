<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;

class LayUserFixtures extends AbstractDataFixture
{
    public function doLoad(FixtureService $fixtureService): void
    {
        foreach (CourtOrderReportType::cases() as $type) {
            foreach (CourtOrderKind::cases() as $kind) {
                if ($type === CourtOrderReportType::OPG104 && $kind !== CourtOrderKind::Single) {
                    continue;
                }
                for ($i = 1; $i <= 10; ++$i) {
                    $this->instantiateForTypeAndKind($type, $kind, $i);
                }
            }
        }
    }

    private function instantiateForTypeAndKind(CourtOrderReportType $type, CourtOrderKind $kind, int $i): void
    {
        $this->instantiateWithDeterministicLogin(1, $this->makeScenario($type, $kind, $i, false), 'publicguardian.gov.uk');
        $this->instantiateWithDeterministicLogin(3, $this->makeScenario($type, $kind, $i, true), 'publicguardian.gov.uk');
    }

    private function makeScenario(CourtOrderReportType $type, CourtOrderKind $kind, int $i, bool $multiClient): Scenario
    {
        $deputySet = $kind !== CourtOrderKind::Dual ? $this->getDeputySet($type, $kind, $i, $multiClient) : $this->getDualDeputySet($i, $multiClient, $type, false);
        $siblingSet = $kind !== CourtOrderKind::Dual ? null : $this->getDualDeputySet($i, $multiClient, $type, true);
        return new Scenario(new CourtOrderDescriptor(
            $deputySet,
            $type,
            single: $kind === CourtOrderKind::Single,
            siblingDeputySet: $siblingSet
        ));
    }

    private function getDeputySet(CourtOrderReportType $type, CourtOrderKind $kind, int $i, bool $multiClient): DeputySet
    {
        $label = strtolower($type->value);
        $label = match ($kind) {
            CourtOrderKind::Single => $label,
            CourtOrderKind::Hybrid => "{$label}-4",
            CourtOrderKind::Dual => new \LogicException("Use getDualDeputySet"),
        };
        $multi = $multiClient ? 'multi-' : '';
        return DeputySet::oneLay("lay-{$multi}{$label}-user-{$i}");
    }

    private function getDualDeputySet(int $i, bool $multiClient, CourtOrderReportType $type, bool $sibling): DeputySet
    {
        $multi = $multiClient ? 'multi-' : '';
        $label = strtolower($type->value);
        $label = "lay-{$multi}{$label}-dual";
        $specific = $sibling ? '-hw' : '-pfa';
        return DeputySet::manyLay("{$label}-user-{$i}", "{$label}{$specific}-user-{$i}");
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return $workspace === 'training' && in_array($environment, ['dev', 'local']);
    }
}
