<?php

namespace App\Validator\Constraints;

use App\Validator\Constraints\CommonPassword;
use App\Validator\Constraints\CommonPasswordValidator;
use PHPUnit\Framework\TestCase;

class CommonPasswordValidatorTest extends TestCase
{

    /**
     * @param string $expectedMessage The expected message on a validation violation, if any.
     * @return CommonPasswordValidator
     */
    public function configureValidator($expectedMessage = null)
    {
        // mock the violation builder
        $builder = $this->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('addViolation'))
            ->getMock();

        // mock the validator context
        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(array('buildViolation'))
            ->getMock();

        if ($expectedMessage) {
            $builder->expects($this->once())
                ->method('addViolation');

            $context->expects($this->once())
                ->method('buildViolation')
                ->with($this->equalTo($expectedMessage))
                ->will($this->returnValue($builder));
        } else {
            $context->expects($this->never())
                ->method('buildViolation');
        }

        // initialize the validator with the mocked context
        $validator = new CommonPasswordValidator();
        $validator->initialize($context);

        // return the SomeConstraintValidator
        return $validator;
    }

    /**
     * Verify a constraint message is triggered when value is invalid.
     */
    public function testValidateOnInvalid()
    {
        $constraint = new CommonPassword();
        $validator = $this->configureValidator($constraint->message);

        $validator->validate('Password123', $constraint);
    }

    /**
     * Verify no constraint message is triggered when value is valid.
     */
    public function testValidateOnValid()
    {
        $constraint = new CommonPassword();
        $validator = $this->configureValidator();

        $validator->validate('Aformidablepw876!', $constraint);
    }
}