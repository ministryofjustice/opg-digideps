<?php

declare(strict_types=1);

namespace Tests\App\Entity\Report;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class IncomeReceivedOnClientsBehalfTest extends TestCase
{
    /**
     * @test
     * @dataProvider invalidDataProvider
     */
    public function testValidation($incomeType, $amount, $amountDontKnow)
    {
        $sut = (new IncomeReceivedOnClientsBehalf())
            ->setIncomeType($incomeType)
            ->setAmount($amount)
            ->setAmountDontKnow($amountDontKnow);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        $this->assertCount(1, $result);
    }

    public function invalidDataProvider()
    {
        return [
            'Fails when $amountDontKnow is true and $incomeType and $amount is null' => [
                null,
                null,
                true,
            ],
            'Fails when $incomeType is a non-empty string and $amount is null and $amountDontKnow is false' => [
                'A type of income',
                null,
                false,
            ],
            'Fails when $amount is a number, $incomeType is null and $amountDontKnow is false' => [
                null,
                20,
                false,
            ],
            'Fails when $amount is a number, $incomeType is a non-empty string and $amountDontKnow is true' => [
                'Some income type',
                20,
                true,
            ],
        ];
    }
}
