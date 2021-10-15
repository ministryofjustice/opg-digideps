<?php

declare(strict_types=1);

namespace Tests\App\Entity\Report;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class IncomeReceivedOnClientsBehalfTest extends TestCase
{
    public function testValidation($city, $state, $expected)
    {
        // Arrange
        $sut = (new IncomeReceivedOnClientsBehalf())
            ->setAmount()
            ->setAmountDontKnow()
            ->setIncomeType()
            ->setClientBenefitsCheck();

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        // Act
        $result = $validator->validate($sut);

        // Assert
        $this->assertEquals($expected, 0 == count($result));
    }

    public function invalidDataProvider()
    {
        return [
            'Fails when $amountDontKnow is true and $incomeType is null' => [],
            'Fails when $incomeType is a non-empty string and $amount is null' => [],
            'Fails when $amount is a non-empty string and $incomeType is null' => [],
        ];
    }
}
