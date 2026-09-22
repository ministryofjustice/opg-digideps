<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Validator\Constraints;

use OPG\Digideps\Frontend\Validator\Constraints\CommonPassword;
use OPG\Digideps\Frontend\Validator\Constraints\CommonPasswordValidator;
use PHPUnit\Framework\TestCase;

class CommonPasswordValidatorTest extends TestCase
{
    /**
     * @param ?string $expectedMessage the expected message on a validation violation, if any
     */
    public function configureValidator(?string $expectedMessage = null): CommonPasswordValidator
    {
        // mock the violation builder
        $builder = $this->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->onlyMethods(['addViolation'])
            ->getMock();

        // mock the validator context
        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->onlyMethods(['buildViolation'])
            ->getMock();

        if ($expectedMessage) {
            $builder->expects(self::once())->method('addViolation');

            $context->expects(self::once())
                ->method('buildViolation')
                ->with(self::equalTo($expectedMessage))
                ->will(self::returnValue($builder));
        } else {
            $context->expects(self::never())->method('buildViolation');
        }

        // initialize the validator with the mocked context
        $validator = new CommonPasswordValidator(
            __DIR__ . '/../../TestData/commonpasswords.txt',
            '',
            false
        );
        $validator->initialize($context);

        // return the SomeConstraintValidator
        return $validator;
    }

    /**
     * Verify a constraint message is triggered when value is invalid.
     */
    public function testValidateOnInvalid(): void
    {
        $constraint = new CommonPassword();
        $validator = $this->configureValidator($constraint->message);

        $validator->validate('Password123', $constraint);
    }

    /**
     * Verify no constraint message is triggered when value is valid.
     */
    public function testValidateOnValid(): void
    {
        $constraint = new CommonPassword();
        $validator = $this->configureValidator();

        $validator->validate('Aformidablepw876!', $constraint);
    }
}
