<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Validator\Constraints\ProfDeputyCostsEstimate;

use OPG\Digideps\Frontend\Entity\Report\ProfDeputyEstimateCost;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Validator\Constraints\ProfDeputyCostsEstimate\CostBreakdownNotGreaterThanTotal;
use OPG\Digideps\Frontend\Validator\Constraints\ProfDeputyCostsEstimate\CostBreakdownNotGreaterThanTotalValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CostBreakdownNotGreaterThanTotalValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $context;
    private Report $data;

    private ConstraintValidator $sut;

    public function setUp(): void
    {
        $this->data = new Report();
        $this->context = $this->createMock(ExecutionContextInterface::class);

        $this->sut = new CostBreakdownNotGreaterThanTotalValidator();
        $this->sut->initialize($this->context);
    }

    public function testThrowsExceptionOnIncorrectDataType(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->sut->validate(new \stdClass(), new CostBreakdownNotGreaterThanTotal());
    }

    public function testValidatorAddsConstraintIfBreakdownTotalGreaterThanAmountItCanExceed(): void
    {
        $this->setTotalCostEstimate()
            ->setIndividualBreakdownCosts('13.01')
            ->assertConstraintWillBeApplied()
            ->invokeTest();
    }

    public function testValidatorIgnoresConstraintIfBreakdownTotalNotGreaterThanAmountItCanExceed(): void
    {
        foreach (['12.99', '13.00'] as $costVariation) {
            $this->setTotalCostEstimate()
                ->setIndividualBreakdownCosts($costVariation)
                ->assertConstraintWillNotBeApplied()
                ->invokeTest();
        }
    }

    private function setTotalCostEstimate(): static
    {
        $this->data->setProfDeputyManagementCostAmount(43.0);

        return $this;
    }

    private function setIndividualBreakdownCosts(string $costBeta): static
    {
        $breakdownAlpha = new ProfDeputyEstimateCost('1', '30', false, null);
        $breakdownBeta = new ProfDeputyEstimateCost('2', $costBeta, false, null);

        $this->data->setProfDeputyEstimateCosts([$breakdownAlpha, $breakdownBeta]);

        return $this;
    }

    private function assertConstraintWillBeApplied(): static
    {
        $this->context->expects($this->once())->method('addViolation');

        return $this;
    }

    private function assertConstraintWillNotBeApplied(): static
    {
        $this->context->expects($this->never())->method('addViolation');

        return $this;
    }

    private function invokeTest(): void
    {
        $this->sut->validate($this->data, new CostBreakdownNotGreaterThanTotal());
    }
}
