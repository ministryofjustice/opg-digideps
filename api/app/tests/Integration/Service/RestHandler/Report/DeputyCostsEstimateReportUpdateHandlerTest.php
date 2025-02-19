<?php

namespace App\Tests\Integration\Service\RestHandler\Report;

use App\Entity\Client;
use App\Entity\Report\ProfDeputyEstimateCost;
use App\Entity\Report\Report;
use App\Service\RestHandler\Report\DeputyCostsEstimateReportUpdateHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class DeputyCostsEstimateReportUpdateHandlerTest extends TestCase
{
    /** @var DeputyCostsEstimateReportUpdateHandler */
    private $sut;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    private $em;

    /** @var Report|\PHPUnit_Framework_MockObject_MockObject */
    private $report;

    public function setUp(): void
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, $date, $date])
            ->setMethods(['updateSectionsStatusCache'])
            ->getMock();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new DeputyCostsEstimateReportUpdateHandler($this->em);
    }

    /** @dataProvider costEstimateDataProvider
     */
    public function testUpdatesSingularFields($field, $data, $expected)
    {
        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo($field, $expected);
    }

    public function costEstimateDataProvider()
    {
        return [
            ['profDeputyCostsEstimateHowCharged', ['prof_deputy_costs_estimate_how_charged' => 'new-value'], 'new-value'],
            ['profDeputyCostsEstimateManagementCostAmount', ['prof_deputy_management_cost_amount' => 100.00], 100.00],
        ];
    }

    public function testResetsAssessedAnswersWhenFixedCostIsSet()
    {
        $data['prof_deputy_costs_estimate_how_charged'] = 'fixed';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info')
            ->setProfDeputyEstimateCosts(new ArrayCollection([new ProfDeputyEstimateCost()]))
            ->setProfDeputyCostsEstimateManagementCostAmount(100.00);

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHowCharged', 'fixed');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', null);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', null);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateManagementCostAmount', null);
        $this->assertTrue($this->report->getProfDeputyEstimateCosts()->isEmpty());
    }

    public function testPreservesAssessedAnswersWhenAssessedCostIsSet()
    {
        $data['prof_deputy_costs_estimate_how_charged'] = 'assessed';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info')
            ->setProfDeputyEstimateCosts(new ArrayCollection([new ProfDeputyEstimateCost()]))
            ->setProfDeputyCostsEstimateManagementCostAmount(100.00);

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHowCharged', 'assessed');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'yes');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', 'more info');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateManagementCostAmount', 100.00);
        $this->assertFalse($this->report->getProfDeputyEstimateCosts()->isEmpty());
    }

    /**
     * @dataProvider getInvalidCostEstimateInputs
     */
    public function testThrowsExceptionUpdatingCostEstimatesWithInsufficientData($data)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->invokeHandler($data);
    }

    /**
     * @return array
     */
    public function getInvalidCostEstimateInputs()
    {
        return [
            [['prof_deputy_estimate_costs' => [['amount' => '21', 'has_more_details' => false, 'more_details' => null]]]],
            [['prof_deputy_estimate_costs' => [['prof_deputy_estimate_cost_type_id' => 'foo', 'has_more_details' => false, 'more_details' => null]]]],
            [['prof_deputy_estimate_costs' => [['prof_deputy_estimate_cost_type_id' => 'foo', 'amount' => '21', 'has_more_details' => true]]]],
            [['prof_deputy_estimate_costs' => [['prof_deputy_estimate_cost_type_id' => 'foo', 'amount' => '21', 'more_details' => 'info']]]],
        ];
    }

    public function testUpdatesExistingOrCreatesNewProfDeputyEstimateCost()
    {
        $existing = new ProfDeputyEstimateCost();
        $existing
            ->setReport($this->report)
            ->setProfDeputyEstimateCostTypeId('forms-documents')
            ->setAmount('22.99')
            ->setHasMoreDetails(true);

        $this->report->addProfDeputyEstimateCost($existing);

        $data['prof_deputy_estimate_costs'] = [
            ['prof_deputy_estimate_cost_type_id' => 'contact-client', 'amount' => '30.32', 'has_more_details' => false, 'more_details' => null],
            ['prof_deputy_estimate_cost_type_id' => 'forms-documents', 'amount' => '33.98', 'has_more_details' => true, 'more_details' => 'updated-details'],
        ];

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->ensureEachProfDeputyEstimateCostWillBePersisted(count($data['prof_deputy_estimate_costs']));
        $this->invokeHandler($data);

        $this->assertCount(2, $this->report->getProfDeputyEstimateCosts());
        $this->assertExistingProfDeputyEstimateCostIsUpdated();
        $this->assertNewProfDeputyEstimateCostIsCreated();
    }

    public function testUpdatesMoreInformation()
    {
        $data['prof_deputy_costs_estimate_has_more_info'] = 'yes';
        $data['prof_deputy_costs_estimate_more_info_details'] = 'more info';

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'yes');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', 'more info');
    }

    public function testRemovesMoreInfoDetailsWhenNoLongerHasMoreInfo()
    {
        $data['prof_deputy_costs_estimate_has_more_info'] = 'no';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info');

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'no');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', null);
    }

    public function testPreservesMoreInfoDetailsWhenHasMoreInfo()
    {
        $data['prof_deputy_costs_estimate_has_more_info'] = 'yes';
        $data['prof_deputy_costs_estimate_more_info_details'] = 'more info updated';

        $this->report
            ->setProfDeputyCostsEstimateHasMoreInfo('yes')
            ->setProfDeputyCostsEstimateMoreInfoDetails('more info');

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHasMoreInfo', 'yes');
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateMoreInfoDetails', 'more info updated');
    }

    private function ensureSectionStatusCacheWillBeUpdated()
    {
        $this
            ->report
            ->expects($this->once())
            ->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
    }

    private function ensureEachProfDeputyEstimateCostWillBePersisted($count)
    {
        $this
            ->em
            ->expects($this->exactly($count))
            ->method('persist');
    }

    private function invokeHandler(array $data)
    {
        $this->sut->handle($this->report, $data);
    }

    private function assertReportFieldValueIsEqualTo($field, $expected)
    {
        $getter = sprintf('get%s', ucfirst($field));
        $this->assertEquals($expected, $this->report->$getter());
    }

    private function assertExistingProfDeputyEstimateCostIsUpdated()
    {
        $profDeputyEstimateCost = $this->report->getProfDeputyEstimateCostByTypeId('forms-documents');

        $this->assertSame($this->report, $profDeputyEstimateCost->getReport());
        $this->assertEquals('33.98', $profDeputyEstimateCost->getAmount());
        $this->assertEquals(true, $profDeputyEstimateCost->getHasMoreDetails());
        $this->assertEquals('updated-details', $profDeputyEstimateCost->getMoreDetails());
    }

    private function assertNewProfDeputyEstimateCostIsCreated()
    {
        $profDeputyEstimateCost = $this->report->getProfDeputyEstimateCostByTypeId('contact-client');
        $this->assertSame($this->report, $profDeputyEstimateCost->getReport());
        $this->assertEquals('30.32', $profDeputyEstimateCost->getAmount());
        $this->assertEquals(false, $profDeputyEstimateCost->getHasMoreDetails());
        $this->assertEquals(null, $profDeputyEstimateCost->getMoreDetails());
    }
}
