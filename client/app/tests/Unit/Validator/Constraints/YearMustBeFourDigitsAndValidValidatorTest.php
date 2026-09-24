<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Validator\Constraints;

use OPG\Digideps\Frontend\TestHelpers\ClientHelpers;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\Validator\Constraints\YearMustBeFourDigitsAndValid;
use OPG\Digideps\Frontend\Validator\Constraints\YearMustBeFourDigitsAndValidValidator;
use PHPUnit\Framework\TestCase;

class YearMustBeFourDigitsAndValidValidatorTest extends TestCase
{
    private function configureValidator(?string $expectedMessage = null): YearMustBeFourDigitsAndValidValidator
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
        $validator = new YearMustBeFourDigitsAndValidValidator();
        $validator->initialize($context);

        return $validator;
    }

    /**
     * Verify a constraint message is triggered when court date year is invalid.
     */
    public function testValidateOnInvalidCourtDate(): void
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
    public function testValidateOnValidCourtDate(): void
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
    public function testValidateOnInvalidYear(): void
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
    public function testValidateOnValidYear(): void
    {
        $constraint = new YearMustBeFourDigitsAndValid();
        $validator = $this->configureValidator();

        $client = ReportHelpers::createReport();
        $client->setStartDate(new \DateTime('today'));
        $client->setEndDate(new \DateTime('today')->modify('+1 year'));

        $validator->validate($client, $constraint);
    }
}
