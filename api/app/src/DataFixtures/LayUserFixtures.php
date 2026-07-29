<?php

namespace OPG\Digideps\Backend\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;

class LayUserFixtures extends AbstractDataFixture
{
    public function doLoad(ObjectManager $manager): void
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
        return new Scenario(new CourtOrderDescriptor(
            DeputySet::oneLay($this->getDeputyId($type, $kind, $i, $multiClient)),
            $type,
            single: $kind === CourtOrderKind::Single,
            siblingDeputySet: $kind === CourtOrderKind::Dual ? DeputySet::oneLay($this->getDeputyId(CourtOrderReportType::OPG104, $kind, $i, $multiClient, $type)) : null
        ));
    }

    private function getDeputyId(CourtOrderReportType $type, CourtOrderKind $kind, int $i, bool $multiClient, ?CourtOrderReportType $dualType = null): string
    {
        $label = strtolower($type->value);
        $dualLabel = $type === CourtOrderReportType::OPG104 ? ($dualType === CourtOrderReportType::OPG102 ? '-2' : '-3') : '';
        $label = match ($kind) {
            CourtOrderKind::Single => $label,
            CourtOrderKind::Hybrid => "{$label}-4",
            CourtOrderKind::Dual => "{$label}{$dualLabel}-dual",
        };
        $multi = $multiClient ? 'multi-' : '';
        return "lay-{$multi}{$label}-user-{$i}";
    }

    /** @return String[] */
    protected function getEnvironments(): array
    {
        return ['dev', 'local'];
    }
}
