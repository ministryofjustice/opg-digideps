<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints;

use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf as NdrIncomeReceivedOnClientsBehalf;
use App\TestHelpers\NdrHelpers;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use App\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheckValidator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NdrClientBenefitsCheckValidatorTest extends TestCase
{
    private NdrClientBenefitsCheck $ndrClientBenefitsCheck;

    /**
     * @var ExecutionContextInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $ndrContext;

    /**
     * @var ConstraintViolationBuilderInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $ndrViolationBuilder;

    private ClientBenefitsCheckValidator $ndrSut;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $ndr = NdrHelpers::createNdr();
        $this->ndrClientBenefitsCheck = (new NdrClientBenefitsCheck())
            ->setNdr($ndr)
            ->setTypesOfIncomeReceivedOnClientsBehalf(new ArrayCollection());

        $this->ndrContext = $this->createMock(ExecutionContextInterface::class);
        $this->ndrContext
            ->expects($this->atLeastOnce())
            ->method('getObject')
            ->willReturn($this->ndrClientBenefitsCheck);

        $this->ndrViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->ndrSut = new ClientBenefitsCheckValidator();
        $this->ndrSut->initialize($this->ndrContext);
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
        $this->ndrClientBenefitsCheck->setWhenLastCheckedEntitlement($whenLastChecked);

        return $this;
    }

    private function setDoOthersReceiveIncomeOnClientsBehalf(string $doOthersReceiveIncomeOnClientsBehalf)
    {
        $this->ndrClientBenefitsCheck->setDoOthersReceiveIncomeOnClientsBehalf($doOthersReceiveIncomeOnClientsBehalf);

        return $this;
    }

    private function addEmptyIncomeTypeToClientBenefitsCheck()
    {
        $this->ndrClientBenefitsCheck->addTypeOfMoneyReceivedOnClientsBehalf(new NdrIncomeReceivedOnClientsBehalf());

        return $this;
    }

    private function invokeTest($value)
    {
        $this->ndrSut->validate($value, new ClientBenefitsCheckConstraint());
    }

    private function setContextPropertyName(string $propertyName)
    {
        $this->ndrContext
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn($propertyName);

        return $this;
    }

    private function expectViolationAdded(string $transId)
    {
        $this->ndrContext
            ->expects($this->atLeastOnce())
            ->method('buildViolation')
            ->with($this->equalTo($transId))
            ->willReturn($this->ndrViolationBuilder);

        $this->ndrViolationBuilder
            ->expects($this->atLeastOnce())
            ->method('setTranslationDomain')
            ->with($this->anything())
            ->willReturn($this->ndrViolationBuilder);

        $this->ndrViolationBuilder
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with($this->anything())
            ->willReturn($this->ndrViolationBuilder);

        $this->ndrViolationBuilder
            ->expects($this->atLeastOnce())
            ->method('addViolation');

        return $this;
    }
}
