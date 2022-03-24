<?php

namespace App\Tests\Unit\Entity;

use App\Entity\CasRec;
use App\Entity\Report\Report;
use PHPUnit\Framework\TestCase;

class CasRecTest extends TestCase
{
    public function getReportTypeByOrderTypeProvider()
    {
        // follow order in https://opgtransform.atlassian.net/wiki/spaces/DEPDS/pages/135266255/Report+variations
        return [
            Report::LAY_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', CasRec::REALM_LAY,  Report::LAY_PFA_LOW_ASSETS_TYPE],
            Report::LAY_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', CasRec::REALM_LAY, Report::LAY_PFA_HIGH_ASSETS_TYPE],
            Report::LAY_HW_TYPE => ['opg104', 'hw', CasRec::REALM_LAY, Report::LAY_HW_TYPE],
            Report::LAY_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', CasRec::REALM_LAY, Report::LAY_COMBINED_LOW_ASSETS_TYPE],
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', CasRec::REALM_LAY,  Report::LAY_COMBINED_HIGH_ASSETS_TYPE],
            Report::PA_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', CasRec::REALM_PA, Report::PA_PFA_LOW_ASSETS_TYPE],
            Report::PA_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', CasRec::REALM_PA, Report::PA_PFA_HIGH_ASSETS_TYPE],
            Report::PA_HW_TYPE => ['opg104', 'hw', CasRec::REALM_PA, Report::PA_HW_TYPE],
            Report::PA_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', CasRec::REALM_PA, Report::PA_COMBINED_LOW_ASSETS_TYPE],
            Report::PA_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', CasRec::REALM_PA, Report::PA_COMBINED_HIGH_ASSETS_TYPE],
            Report::PROF_PFA_LOW_ASSETS_TYPE => ['opg103', 'pfa', CasRec::REALM_PROF, Report::PROF_PFA_LOW_ASSETS_TYPE],
            Report::PROF_PFA_HIGH_ASSETS_TYPE => ['opg102', 'pfa', CasRec::REALM_PROF, Report::PROF_PFA_HIGH_ASSETS_TYPE],
            Report::PROF_HW_TYPE => ['opg104', 'hw', CasRec::REALM_PROF, Report::PROF_HW_TYPE],
            Report::PROF_COMBINED_LOW_ASSETS_TYPE => ['opg103', 'hw', CasRec::REALM_PROF, Report::PROF_COMBINED_LOW_ASSETS_TYPE],
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE => ['opg102', 'hw', CasRec::REALM_PROF, Report::PROF_COMBINED_HIGH_ASSETS_TYPE],
        ];
    }

    /**
     * @dataProvider getReportTypeByOrderTypeProvider
     */
    public function testGetReportTypeByOrderType($reportType, $orderType, $realm, $expectedType)
    {
        $this->assertEquals($expectedType, CasRec::getReportTypeByOrderType($reportType, $orderType, $realm));
    }
}
