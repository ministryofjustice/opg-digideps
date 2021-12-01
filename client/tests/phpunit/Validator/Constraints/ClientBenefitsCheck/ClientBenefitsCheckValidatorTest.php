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
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientBenefitsCheckValidatorTest extends TestCase
{
    /** @var ConstraintValidator */
    private $sut;

    /** @var ExecutionContextInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var ClientBenefitsCheck */
    private $object;

    /** @var TranslatorInterface | \PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ConstraintViolationBuilderInterface | \PHPUnit\Framework\MockObject\MockObject */
    private $violationBuilder;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $report = ReportHelpers::createReport();
        $this->object = (new ClientBenefitsCheck())
            ->setReport($report)
            ->setTypesOfIncomeReceivedOnClientsBehalf(new ArrayCollection());

        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->context
            ->expects($this->atLeastOnce())
            ->method('getObject')
            ->willReturn($this->object);

        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->sut = new ClientBenefitsCheckValidator($this->translator);
        $this->sut->initialize($this->context);
    }

    /**
     * @dataProvider whenLastCheckedEntitlementValueProvider
     * @test
     */
    public function validatorAddsConstraintIfPropertyIsWhenLastCheckedEntitlement($value)
    {
        $this->expectException(\RuntimeException::class);

        $this->setContextPropertyName('whenLastCheckedEntitlement')
            ->expectTranslatorCalledWith('form.whenLastChecked.errors.noOptionSelected')
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
            ->expectTranslatorCalledWith($transId)
            ->expectViolationAdded()
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
            ->expectTranslatorCalledWith($transId)
            ->expectViolationAdded()
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
            ->expectTranslatorCalledWith($transId)
            ->expectViolationAdded()
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
            ->expectTranslatorCalledWith('form.incomeDetails.errors.missingIncome')
            ->expectViolationAdded()
            ->invokeTest(null);
    }

    //Helpers

    private function setWhenLastCheckedEntitlementTo(string $whenLastChecked)
    {
        $this->object->setWhenLastCheckedEntitlement($whenLastChecked);

        return $this;
    }

    private function setDoOthersReceiveIncomeOnClientsBehalf(string $doOthersReceiveIncomeOnClientsBehalf)
    {
        $this->object->setDoOthersReceiveIncomeOnClientsBehalf($doOthersReceiveIncomeOnClientsBehalf);

        return $this;
    }

    private function addEmptyIncomeTypeToClientBenefitsCheck()
    {
        $this->object->addTypeOfIncomeReceivedOnClientsBehalf(new IncomeReceivedOnClientsBehalf());

        return $this;
    }

    private function invokeTest($value)
    {
        $this->sut->validate($value, new ClientBenefitsCheckConstraint());
    }

    private function setContextPropertyName(string $propertyName)
    {
        $this->context
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn($propertyName);

        return $this;
    }

    private function expectViolationAdded()
    {
        $this->context
            ->expects($this->atLeastOnce())
            ->method('buildViolation')
            ->with($this->equalTo('An error message'))
            ->willReturn($this->violationBuilder);

        $this->violationBuilder
            ->expects($this->atLeastOnce())
            ->method('addViolation');

        return $this;
    }

    private function expectTranslatorCalledWith(string $transId)
    {
        $this->translator
            ->expects($this->any())
            ->method('trans')
            ->with($this->equalTo($transId))
            ->willReturn('An error message');

        return $this;
    }
}
