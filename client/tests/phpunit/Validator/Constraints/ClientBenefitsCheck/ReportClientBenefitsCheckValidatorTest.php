<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\TestHelpers\ReportHelpers;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheckValidator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReportClientBenefitsCheckValidatorTest extends TestCase
{
    /** @var ConstraintValidator */
    private $reportSut;

    /** @var ExecutionContextInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $reportContext;

    private ClientBenefitsCheck $reportClientBenefitsCheck;

    /** @var ConstraintViolationBuilderInterface | \PHPUnit\Framework\MockObject\MockObject */
    private $reportViolationBuilder;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $report = ReportHelpers::createReport();
        $this->reportClientBenefitsCheck = (new ClientBenefitsCheck())
            ->setReport($report)
            ->setTypesOfIncomeReceivedOnClientsBehalf(new ArrayCollection());

        $this->reportContext = $this->createMock(ExecutionContextInterface::class);
        $this->reportContext
            ->expects($this->atLeastOnce())
            ->method('getObject')
            ->willReturn($this->reportClientBenefitsCheck);

        $this->reportViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->reportSut = new ClientBenefitsCheckValidator();
        $this->reportSut->initialize($this->reportContext);
    }

    /**
     * @dataProvider whenLastCheckedEntitlementValueProvider
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsWhenLastCheckedEntitlement($value)
    {
        $this->setContextPropertyName('whenLastCheckedEntitlement')
            ->expectViolationAdded('form.whenLastChecked.errors.noOptionSelected')
            ->invokeTest($value);
    }

    public function whenLastCheckedEntitlementValueProvider()
    {
        return [
            'null' => [null],
            'string not in accepted list' => ['Ziggy'],
            'int' => [44],
        ];
    }

    /**
     * @dataProvider dateLastCheckedEntitlementValueProvider
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsDateLastCheckedEntitlement($value, $transId)
    {
        $this->setContextPropertyName('dateLastCheckedEntitlement')
            ->setWhenLastCheckedEntitlementTo('haveChecked')
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    public function dateLastCheckedEntitlementValueProvider()
    {
        return [
            'null' => [null, 'form.whenLastChecked.errors.missingDate'],
            'future date' => [new \DateTime('+1 day'), 'form.whenLastChecked.errors.futureDate'],
        ];
    }

    /**
     * @dataProvider neverCheckedExplanationValueProvider
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsNeverCheckedExplanation($value, $transId)
    {
        $this->setContextPropertyName('neverCheckedExplanation')
            ->setWhenLastCheckedEntitlementTo('neverChecked')
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    public function neverCheckedExplanationValueProvider()
    {
        return [
            'null' => [null, 'form.whenLastChecked.errors.missingExplanation'],
            'future date' => ['aaa', 'form.whenLastChecked.errors.explanationTooShort'],
        ];
    }

    /**
     * @dataProvider dontKnowIncomeExplanationValueProvider
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsDontKnowIncomeExplanation($value, $transId)
    {
        $this->setContextPropertyName('dontKnowIncomeExplanation')
            ->setDoOthersReceiveIncomeOnClientsBehalf('dontKnow')
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    public function dontKnowIncomeExplanationValueProvider()
    {
        return [
            'null' => [null, 'form.incomeOnClientsBehalf.errors.missingExplanation'],
            'future date' => ['aaa', 'form.incomeOnClientsBehalf.errors.explanationTooShort'],
        ];
    }

    /**
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsTypesOfIncomeReceivedOnClientsBehalf()
    {
        $this->setContextPropertyName('typesOfIncomeReceivedOnClientsBehalf')
            ->addEmptyIncomeTypeToClientBenefitsCheck()
            ->expectViolationAdded('form.incomeDetails.errors.missingIncome')
            ->invokeTest(null);
    }

    //Helpers

    private function setWhenLastCheckedEntitlementTo(string $whenLastChecked)
    {
        $this->reportClientBenefitsCheck->setWhenLastCheckedEntitlement($whenLastChecked);

        return $this;
    }

    private function setDoOthersReceiveIncomeOnClientsBehalf(string $doOthersReceiveIncomeOnClientsBehalf)
    {
        $this->reportClientBenefitsCheck->setDoOthersReceiveIncomeOnClientsBehalf($doOthersReceiveIncomeOnClientsBehalf);

        return $this;
    }

    private function addEmptyIncomeTypeToClientBenefitsCheck()
    {
        $this->reportClientBenefitsCheck->addTypeOfIncomeReceivedOnClientsBehalf(new IncomeReceivedOnClientsBehalf());

        return $this;
    }

    private function invokeTest($value)
    {
        $this->reportSut->validate($value, new ClientBenefitsCheckConstraint());
    }

    private function setContextPropertyName(string $propertyName)
    {
        $this->reportContext
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn($propertyName);

        return $this;
    }

    private function expectViolationAdded(string $transId)
    {
        $this->reportContext
            ->expects($this->atLeastOnce())
            ->method('buildViolation')
            ->with($this->equalTo($transId))
            ->willReturn($this->reportViolationBuilder);

        $this->reportViolationBuilder
            ->expects($this->atLeastOnce())
            ->method('setTranslationDomain')
            ->with($this->anything())
            ->willReturn($this->reportViolationBuilder);

        $this->reportViolationBuilder
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with($this->anything())
            ->willReturn($this->reportViolationBuilder);

        $this->reportViolationBuilder
            ->expects($this->atLeastOnce())
            ->method('addViolation');

        return $this;
    }
}
