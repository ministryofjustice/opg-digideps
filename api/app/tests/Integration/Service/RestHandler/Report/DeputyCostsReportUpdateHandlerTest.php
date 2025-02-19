<?php

namespace App\Tests\Integration\Service\RestHandler\Report;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Service\RestHandler\Report\DeputyCostsReportUpdateHandler;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class DeputyCostsReportUpdateHandlerTest extends TestCase
{
    /** @var DeputyCostsReportUpdateHandler */
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

        $this->sut = new DeputyCostsReportUpdateHandler($this->em);
    }

    /** @dataProvider costDataProvider
     */
    public function testUpdatesSingularFields($field, $data, $expected)
    {
        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo($field, $expected);
    }

    public function costDataProvider()
    {
        return [
            ['profDeputyCostsHowCharged', ['prof_deputy_costs_how_charged' => 'new-value'], 'new-value'],
        ];
    }

    public function testResetsInterimCostsWhenFixedCostIsSet()
    {
        $data['prof_deputy_costs_how_charged'] = 'fixed';

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsHowCharged', 'fixed');
        $this->assertTrue($this->report->getProfDeputyInterimCosts()->isEmpty());
    }

    public function testSetFixedCostIsNullWhenHasInterimIsSet()
    {
        $data['prof_deputy_costs_how_charged'] = 'both';

        $this->report
            ->setProfDeputyCostsHasInterim('yes');

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertNull($this->report->getProfDeputyFixedCost());
    }

    public function testInterimCostsAreRemovedWhenNoInterimAnswered()
    {
        $data['prof_deputy_costs_has_interim'] = 'no';

        $this->invokeHandler($data);

        $this->assertTrue($this->report->getProfDeputyInterimCosts()->isEmpty());
    }

    public function testInterimCostsAdded()
    {
        $data['prof_deputy_costs_has_interim'] = 'yes';
        $data['prof_deputy_interim_costs'] = $this->generateValidInterimCosts();

        $this->invokeHandler($data);

        $this->assertNull($this->report->getProfDeputyFixedCost());
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsHasInterim', 'yes');
        $this->assertCount(3, $this->report->getProfDeputyInterimCosts());
    }

    //    public function testPreviousCostsAdded()
    //    {
    //        $data['prof_deputy_costs_has_interim'] = 'yes';
    //        $data['prof_deputy_interim_costs'] = $this->generateValidInterimCosts();
    //
    //        $this->invokeHandler($data);
    //
    //        $this->assertNull($this->report->getProfDeputyFixedCost());
    //        $this->assertReportFieldValueIsEqualTo('profDeputyCostsHasInterim', 'yes');
    //        $this->assertCount(3, $this->report->getProfDeputyInterimCosts());
    //    }

    public function testUpdateFixedCostAmount()
    {
        $data['prof_deputy_fixed_cost'] = '234.56';

        $this->invokeHandler($data);

        $this->assertReportFieldValueIsEqualTo('profDeputyFixedCost', '234.56');
    }

    public function testUpdateCostAmountToScco()
    {
        $data['prof_deputy_costs_reason_beyond_estimate'] = 'some reason';

        $this->invokeHandler($data);

        $this->assertReportFieldValueIsEqualTo('profDeputyCostsReasonBeyondEstimate', 'some reason');
    }

    private function generateValidInterimCosts()
    {
        return [
            ['amount' => '21', 'date' => '2012-01-05'],
            ['amount' => '22', 'date' => '2012-03-11'],
            ['amount' => '23', 'date' => '2012-09-25'],
        ];
    }

    public function testUpdatesExistingOrCreatesNewProfDeputyInterimCost()
    {
        $data['prof_deputy_interim_costs'] = [
            ['amount' => '30.32', 'date' => '01/01/2012'],
            ['amount' => '33.98', 'date' => '02/02/2013'],
        ];

        $this->ensureSectionStatusCacheWillBeUpdated();

        $this->invokeHandler($data);

        $this->assertCount(2, $this->report->getProfDeputyInterimCosts());
        $this->assertNewProfDeputyInterimCostIsCreated();
    }

    private function ensureSectionStatusCacheWillBeUpdated()
    {
        $this
            ->report
            ->expects($this->once())
            ->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PROF_DEPUTY_COSTS]);
    }

    private function ensureEachProfDeputyCostWillBePersisted($count)
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

    private function assertNewProfDeputyInterimCostIsCreated()
    {
        $profDeputyCost = $this->report->getProfDeputyInterimCosts()->first();
        $this->assertSame($this->report, $profDeputyCost->getReport());
        $this->assertEquals('30.32', $profDeputyCost->getAmount());
    }
}
