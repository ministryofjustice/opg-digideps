<?php

namespace App\Validator\Constraints;

use App\TestHelpers\ClientHelpers;
use App\TestHelpers\ReportHelpers;
use PHPUnit\Framework\TestCase;

class YearMustBeFourDigitsAndValidValidatorTest extends TestCase
{
    /**
     * @return YearMustBeFourDigitsAndValidValidator
     */
    public function configureValidator(?string $expectedMessage = null)
    {
        // mock the violation builder
        $builder = $this->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['addViolation'])
            ->getMock();

        // mock the validator context
        $context = $this->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(['buildViolation'])
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
        $validator = new YearMustBeFourDigitsAndValidValidator();
        $validator->initialize($context);

        return $validator;
    }

    /**
     * Verify a constraint message is triggered when court date year is invalid.
     */
    public function testValidateOnInvalidCourtDate()
    {
        $constraint = new YearMustBeFourDigitsAndValid();
        $validator = $this->configureValidator($constraint->message);

        $client = ClientHelpers::createClient();
        $client->setCourtDate(new \DateTime('0116-01-01'));

        $validator->validate($client, $constraint);
    }

    /**
     * Verify no constraint message is triggered when court date year is valid.
     */
    public function testValidateOnValidCourtDate()
    {
        $constraint = new YearMustBeFourDigitsAndValid();
        $validator = $this->configureValidator();

        $client = ClientHelpers::createClient();
        $client->setCourtDate(new \DateTime('today'));

        $validator->validate($client, $constraint);
    }

    /**
     * Verify a constraint message is triggered when reporting period year is invalid.
     */
    public function testValidateOnInvalidYear()
    {
        $constraint = new YearMustBeFourDigitsAndValid();
        $validator = $this->configureValidator($constraint->message);

        $client = ReportHelpers::createReport();
        $client->setStartDate(new \DateTime('0116-01-02'));
        $client->setEndDate(new \DateTime('0117-01-01'));

        $validator->validate($client, $constraint);
    }

    /**
     * Verify no constraint message is triggered when reporting period year is valid.
     */
    public function testValidateOnValidYear()
    {
        $constraint = new YearMustBeFourDigitsAndValid();
        $validator = $this->configureValidator();

        $client = ReportHelpers::createReport();
        $client->setStartDate(new \DateTime('today'));
        $client->setEndDate((new \DateTime('today'))->modify('+1 year'));

        $validator->validate($client, $constraint);
    }
}
