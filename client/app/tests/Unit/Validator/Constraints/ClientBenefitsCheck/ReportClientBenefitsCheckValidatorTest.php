<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Validator\Constraints\ClientBenefitsCheck;

use OPG\Digideps\Frontend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Frontend\Entity\Report\MoneyReceivedOnClientsBehalf;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheck as ClientBenefitsCheckConstraint;
use OPG\Digideps\Frontend\Validator\Constraints\ClientBenefitsCheck\ClientBenefitsCheckValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReportClientBenefitsCheckValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $reportContext;
    private ConstraintViolationBuilderInterface&MockObject $reportViolationBuilder;
    private ClientBenefitsCheck $reportClientBenefitsCheck;

    private ConstraintValidator $sut;

    public function setUp(): void
    {
        $report = ReportHelpers::createReport();
        $this->reportClientBenefitsCheck = new ClientBenefitsCheck()
            ->setReport($report)
            ->setTypesOfMoneyReceivedOnClientsBehalf([]);

        $this->reportContext = self::createMock(ExecutionContextInterface::class);
        $this->reportContext->expects(self::atLeastOnce())
            ->method('getObject')
            ->willReturn($this->reportClientBenefitsCheck);

        $this->reportViolationBuilder = self::createMock(ConstraintViolationBuilderInterface::class);

        $this->sut = new ClientBenefitsCheckValidator();
        $this->sut->initialize($this->reportContext);
    }

    /**
     * @dataProvider whenLastCheckedEntitlementValueProvider
     */
    public function testValidatorAddsConstraintIfPropertyIsWhenLastCheckedEntitlement(string|int|null $value): void
    {
        $this->setContextPropertyName('whenLastCheckedEntitlement')
            ->expectViolationAdded('form.whenLastChecked.errors.noOptionSelected')
            ->invokeTest($value);
    }

    private function invokeTest($value): void
    {
        $this->sut->validate($value, new ClientBenefitsCheckConstraint());
    }

    private function expectViolationAdded(string $transId): static
    {
        $this->reportContext->expects(self::atLeastOnce())
            ->method('buildViolation')
            ->with(self::equalTo($transId))
            ->willReturn($this->reportViolationBuilder);

        $this->reportViolationBuilder->expects(self::atLeastOnce())
            ->method('setTranslationDomain')
            ->with(self::anything())
            ->willReturn($this->reportViolationBuilder);

        $this->reportViolationBuilder->expects(self::atLeastOnce())
            ->method('setParameter')
            ->with(self::anything())
            ->willReturn($this->reportViolationBuilder);

        $this->reportViolationBuilder->expects(self::atLeastOnce())->method('addViolation');

        return $this;
    }

    private function setContextPropertyName(string $propertyName): static
    {
        $this->reportContext->expects(self::atLeastOnce())
            ->method('getPropertyName')
            ->willReturn($propertyName);

        return $this;
    }

    public static function whenLastCheckedEntitlementValueProvider(): array
    {
        return [
            'null' => [null],
            'string not in accepted list' => ['Ziggy'],
            'int' => [44],
        ];
    }

    /**
     * @dataProvider dateLastCheckedEntitlementValueProvider
     */
    public function testValidatorAddsConstraintIfPropertyIsDateLastCheckedEntitlement(?\DateTime $value, string $transId): void
    {
        $this->setContextPropertyName('dateLastCheckedEntitlement')
            ->setWhenLastCheckedEntitlementTo('haveChecked')
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    private function setWhenLastCheckedEntitlementTo(string $whenLastChecked): static
    {
        $this->reportClientBenefitsCheck->setWhenLastCheckedEntitlement($whenLastChecked);

        return $this;
    }

    public static function dateLastCheckedEntitlementValueProvider(): array
    {
        return [
            'null' => [null, 'form.whenLastChecked.errors.missingDate'],
            'future date' => [new \DateTime('+1 day'), 'form.whenLastChecked.errors.futureDate'],
        ];
    }

    public function testValidatorAddsConstraintIfPropertyIsDoOthersReceiveMoneyOnClientsBehalf(): void
    {
        $this->setContextPropertyName('doOthersReceiveMoneyOnClientsBehalf')
            ->setWhenLastCheckedEntitlementTo('haveChecked')
            ->expectViolationAdded('form.moneyOnClientsBehalf.errors.noOptionSelected')
            ->invokeTest(null);
    }

    /**
     * @dataProvider neverCheckedExplanationValueProvider
     */
    public function testValidatorAddsConstraintIfPropertyIsNeverCheckedExplanation(?string $value, string $transId): void
    {
        $this->setContextPropertyName('neverCheckedExplanation')
            ->setWhenLastCheckedEntitlementTo('neverChecked')
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    public static function neverCheckedExplanationValueProvider(): array
    {
        return [
            'null' => [null, 'form.whenLastChecked.errors.missingExplanation'],
            'future date' => ['aaa', 'form.whenLastChecked.errors.explanationTooShort'],
        ];
    }

    /**
     * @dataProvider dontKnowMoneyExplanationValueProvider
     */
    public function testValidatorAddsConstraintIfPropertyIsDontKnowMoneyExplanation(?string $value, string $transId): void
    {
        $this->setContextPropertyName('dontKnowMoneyExplanation')
            ->setDoOthersReceiveMoneyOnClientsBehalf()
            ->expectViolationAdded($transId)
            ->invokeTest($value);
    }

    private function setDoOthersReceiveMoneyOnClientsBehalf(): static
    {
        $this->reportClientBenefitsCheck->setDoOthersReceiveMoneyOnClientsBehalf("dontKnow");

        return $this;
    }

    public static function dontKnowMoneyExplanationValueProvider(): array
    {
        return [
            'null' => [null, 'form.moneyOnClientsBehalf.errors.missingExplanation'],
            'future date' => ['aaa', 'form.moneyOnClientsBehalf.errors.explanationTooShort'],
        ];
    }

    public function testValidatorAddsConstraintIfPropertyIsTypesOfMoneyReceivedOnClientsBehalf(): void
    {
        $this->setContextPropertyName('typesOfMoneyReceivedOnClientsBehalf')
            ->addEmptyMoneyTypeToClientBenefitsCheck()
            ->expectViolationAdded('form.moneyDetails.errors.missingMoney')
            ->invokeTest(null);
    }

    private function addEmptyMoneyTypeToClientBenefitsCheck(): static
    {
        $this->reportClientBenefitsCheck->addTypeOfMoneyReceivedOnClientsBehalf(new MoneyReceivedOnClientsBehalf());

        return $this;
    }
}
