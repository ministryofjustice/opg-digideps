<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\TestHelpers\ReportHelpers;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheckValidator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReportClientBenefitsCheckValidatorTest extends TestCase
{
    /** @var ConstraintValidator */
    private $reportSut;

    /** @var ExecutionContextInterface | PHPUnit_Framework_MockObject_MockObject */
    private $reportContext;

    private ClientBenefitsCheck $reportClientBenefitsCheck;

    /** @var ConstraintViolationBuilderInterface | MockObject */
    private $reportViolationBuilder;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $report = ReportHelpers::createReport();
        $this->reportClientBenefitsCheck = (new ClientBenefitsCheck())
            ->setReport($report)
            ->setTypesOfMoneyReceivedOnClientsBehalf(new ArrayCollection());

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

    private function invokeTest($value)
    {
        $this->reportSut->validate($value, new ClientBenefitsCheckConstraint());
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

    private function setContextPropertyName(string $propertyName)
    {
        $this->reportContext
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn($propertyName);

        return $this;
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

    private function setWhenLastCheckedEntitlementTo(string $whenLastChecked)
    {
        $this->reportClientBenefitsCheck->setWhenLastCheckedEntitlement($whenLastChecked);

        return $this;
    }

    public function dateLastCheckedEntitlementValueProvider()
    {
        return [
            'null' => [null, 'form.whenLastChecked.errors.missingDate'],
            'future date' => [new DateTime('+1 day'), 'form.whenLastChecked.errors.futureDate'],
        ];
    }

    /**
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsDoOthersReceiveMoneyOnClientsBehalf()
    {
        $this->setContextPropertyName('doOthersReceiveMoneyOnClientsBehalf')
            ->setWhenLastCheckedEntitlementTo('haveChecked')
            ->expectViolationAdded('form.moneyOnClientsBehalf.errors.noOptionSelected')
            ->invokeTest(null);
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

    //Helpers

    public function neverCheckedExplanationValueProvider()
    {
        return [
            'null' => [null, 'form.whenLastChecked.errors.missingExplanation'],
            'future date' => ['aaa', 'form.whenLastChecked.errors.explanationTooShort'],
        ];
    }

    /**
     * @dataProvider dontKnowMoneyExplanationValueProvider
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsDontKnowMoneyExplanation($value, $transId)
    {
        $this->setContextPropertyName('dontKnowMoneyExplanation')
            ->setDoOthersReceiveMoneyOnClientsBehalf('dontKnow')
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    private function setDoOthersReceiveMoneyOnClientsBehalf(string $doOthersReceiveMoneyOnClientsBehalf)
    {
        $this->reportClientBenefitsCheck->setDoOthersReceiveMoneyOnClientsBehalf($doOthersReceiveMoneyOnClientsBehalf);

        return $this;
    }

    public function dontKnowMoneyExplanationValueProvider()
    {
        return [
            'null' => [null, 'form.moneyOnClientsBehalf.errors.missingExplanation'],
            'future date' => ['aaa', 'form.moneyOnClientsBehalf.errors.explanationTooShort'],
        ];
    }

    /**
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsTypesOfMoneyReceivedOnClientsBehalf()
    {
        $this->setContextPropertyName('typesOfMoneyReceivedOnClientsBehalf')
            ->addEmptyMoneyTypeToClientBenefitsCheck()
            ->expectViolationAdded('form.moneyDetails.errors.missingMoney')
            ->invokeTest(null);
    }

    private function addEmptyMoneyTypeToClientBenefitsCheck()
    {
        $this->reportClientBenefitsCheck->addTypeOfMoneyReceivedOnClientsBehalf(new MoneyReceivedOnClientsBehalf());

        return $this;
    }
}
