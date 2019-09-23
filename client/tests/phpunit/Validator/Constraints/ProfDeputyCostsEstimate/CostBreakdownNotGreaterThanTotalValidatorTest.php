<?php

namespace AppBundle\Validator\Constraints\ProfDeputyCostsEstimate;

use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CostBreakdownNotGreaterThanTotalValidatorTest extends TestCase
{
    /** @var ConstraintValidator */
    private $sut;

    /** @var ExecutionContextInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var ReportInterface */
    private $data;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->data = new Report();

        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new CostBreakdownNotGreaterThanTotalValidator();
        $this->sut->initialize($this->context);
    }

    public function testThrowsExceptionOnIncorrectDataType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(new \stdClass(), new CostBreakdownNotGreaterThanTotal());
    }

    public function testValidatorAddsConstraintIfBreakdownTotalGreaterThanAmountItCanExceed()
    {
        $this
            ->setTotalCostEstimate(43)
            ->setIndividualBreakdownCosts(30, 13.01)
            ->assertConstraintWillBeApplied()
            ->invokeTest();
    }

    /**
     * @dataProvider breakdownCostVariations
     */
    public function testValidatorIgnoresConstraintIfBreakdownTotalNotGreaterThanAmountItCanExceed($costVariation)
    {
        $this
            ->setTotalCostEstimate(43)
            ->setIndividualBreakdownCosts(30, $costVariation)
            ->assertConstraintWillNotBeApplied()
            ->invokeTest();
    }

    /**
     * @return array
     */
    public function breakdownCostVariations()
    {
        return [
            ['costVariation' => 12.99],
            ['costVariation' => 13.00]
        ];
    }

    /**
     * @param $totalCost
     * @return $this
     */
    private function setTotalCostEstimate($totalCost)
    {
        $this->data->setProfDeputyManagementCostAmount($totalCost);

        return $this;
    }

    /**
     * @param $costAlpha
     * @param $costBeta
     * @return CostBreakdownNotGreaterThanTotalValidatorTest
     */
    private function setIndividualBreakdownCosts($costAlpha, $costBeta)
    {
        $breakdownAlpha = new ProfDeputyEstimateCost(1, $costAlpha, false, null);
        $breakdownBeta = new ProfDeputyEstimateCost(2, $costBeta, false, null);

        $this->data->setProfDeputyEstimateCosts([$breakdownAlpha, $breakdownBeta]);

        return $this;
    }

    /**
     * @return $this
     */
    private function assertConstraintWillBeApplied()
    {
        $this
            ->context
            ->expects($this->once())
            ->method('addViolation');

        return $this;
    }

    /**
     * @return $this
     */
    private function assertConstraintWillNotBeApplied()
    {
        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        return $this;
    }

    private function invokeTest()
    {
        $this->sut->validate($this->data, new CostBreakdownNotGreaterThanTotal());
    }
}
