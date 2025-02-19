<?php

namespace App\Tests\Integration\Entity;

use App\Entity\PreRegistration;
use App\Entity\Report\Report;
use PHPUnit\Framework\TestCase;

class PreRegistrationTest extends TestCase
{
    public function getReportTypeByOrderTypeProvider()
    {
        // follow order in https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
        return [
            Report::LAY_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', PreRegistration::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            Report::LAY_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', PreRegistration::REALM_LAY, Report::LAY_PFA_HIGH_ASSETS_TYPE],
            Report::LAY_HW_TYPE => ['opg104', 'hw', PreRegistration::REALM_LAY, Report::LAY_HW_TYPE],
            Report::LAY_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', PreRegistration::REALM_LAY, Report::LAY_COMBINED_LOW_ASSETS_TYPE],
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', PreRegistration::REALM_LAY,  Report::LAY_COMBINED_HIGH_ASSETS_TYPE],
            Report::PA_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', PreRegistration::REALM_PA, Report::PA_PFA_LOW_ASSETS_TYPE],
            Report::PA_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', PreRegistration::REALM_PA, Report::PA_PFA_HIGH_ASSETS_TYPE],
            Report::PA_HW_TYPE => ['opg104', 'hw', PreRegistration::REALM_PA, Report::PA_HW_TYPE],
            Report::PA_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', PreRegistration::REALM_PA, Report::PA_COMBINED_LOW_ASSETS_TYPE],
            Report::PA_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', PreRegistration::REALM_PA, Report::PA_COMBINED_HIGH_ASSETS_TYPE],
            Report::PROF_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', PreRegistration::REALM_PROF, Report::PROF_PFA_LOW_ASSETS_TYPE],
            Report::PROF_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', PreRegistration::REALM_PROF, Report::PROF_PFA_HIGH_ASSETS_TYPE],
            Report::PROF_HW_TYPE => ['opg104', 'hw', PreRegistration::REALM_PROF, Report::PROF_HW_TYPE],
            Report::PROF_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', PreRegistration::REALM_PROF, Report::PROF_COMBINED_LOW_ASSETS_TYPE],
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', PreRegistration::REALM_PROF, Report::PROF_COMBINED_HIGH_ASSETS_TYPE],
        ];
    }

    /**
     * @dataProvider getReportTypeByOrderTypeProvider
     */
    public function testGetReportTypeByOrderType($reportType, $orderType, $realm, $expectedType)
    {
        $this->assertEquals($expectedType, PreRegistration::getReportTypeByOrderType($reportType, $orderType, $realm));
    }
}
